<?php

namespace app\yiban\model;

use think\Model;

class Bkjw extends Model
{
	protected $table = 'dp_yiban_index';

	public function isBind($stu_id){
		return $this->where('stu_id',$stu_id)->value('pt_pwd');
	}

	public function bind($stu_id,$pt_pwd){

		$stu = $this->where('stu_id',$stu_id)->find();

		if($stu){
			//绑定密码和传入密码不同，意味着用户已经修改密码
			if($stu['pt_pwd'] != $pt_pwd){
				$this->where('id',$stu['id'])->update(['stu_id' => $stu_id, 'pt_pwd' =>$pt_pwd ]);
			}
			return true;
		}else{
			//未绑定，则执行绑定流程
			$this->stu_id = $stu_id;
			$this->pt_pwd = $pt_pwd;
			if($this->save()){
				return $this->id;
			}else{
				return false;
			}
		}
	}

	public function store($stu_id,$score_str){
		$id = $this->where('stu_id',$stu_id)->value('id');
		if($id){
			if($this->where('id',$id)->update(['score'=>$score_str])){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

}