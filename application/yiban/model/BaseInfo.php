<?php

namespace app\yiban\model;

use think\Model;

class BaseInfo extends Model
{
	protected $table = 'dp_yiban_base_info';

	//根据学号获取班级号
	public function getClassId($stu_id){
		$class_id = $this->where('number',$stu_id)->value('class');
        return $class_id;
	}

	//根据班级号获取班级人数
	public function getClassStuCount($class_id){
		return $this->where('class',$class_id)->count();
	}

	/**
	* API : https://openapi.yiban.cn/user/me
	* 根据API返回的数据对象，存储用户信息，若库中存在，则更新头像信息，若不存在，则存储学号、姓名、头像、易班ID等。
	*/
	public function saveStuInfo($infoObj){

		if(isset($infoObj->yb_studentid)){
			$stu = $this->where('number',$infoObj->yb_studentid)->find();
			if($stu){
				//数据存在，更新
				$this->where('number',$infoObj->yb_studentid)->update([
					'head_img' => $infoObj->yb_userhead,
					'yb_userid' =>$infoObj->yb_userid
				]);
				
			}else{
				//不存在，插入
				$this->data([
					'name' => $infoObj->yb_realname,
					'yb_userid' => $infoObj->yb_userid,
					'head_img' => $infoObj->yb_userhead,
					'sex' => ($infoObj->yb_sex == 'M') ? '男' : '女',
					'number' => $infoObj->yb_studentid
				]);
				if(!$this->save()){
					return false;
				}
			}
			$retInfo = $this->where('number',$infoObj->yb_studentid)->column('name,class,sex,major,number,college,head_img');
			return $retInfo[$infoObj->yb_realname];
		}else{
			return false;
		}

	}

	/**
	* 根据学号获取用户相关信息字段：班级号、专业、学院
	* @param $stu_id 学号
	*/
	public function getBaseInfoById($stu_id){
		$retInfo = $this->where('number',$stu_id)->column('number,class,major,college,sex,mobile,class,NJ');
		if($retInfo){
			return $retInfo[$stu_id];
		}else{
			return false;
		}
	}

	/**
	* 根据学号获取用户相关信息字段：班级号、专业、学院
	* @param $stu_id 学号
	* @return 是否更新成功(bool)
	*/
	public function updateMobileById($stu_id,$mobile){
		return $this->where('number',$stu_id)->update(['mobile'=>$mobile]);
	}
}