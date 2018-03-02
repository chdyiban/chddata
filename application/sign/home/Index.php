<?php
namespace app\sign\home;

use app\index\controller\Home;
use think\Request;
use think\Db;
use app\yiban\model\BaseInfo as BaseModel;
/**
 * 点名上传控制器
 * @package app\sign\home
 */
class Index extends Home
{
	public function getNotSignList(){
        $stu_id = input('get.stu_id');
        // $stu_id = '2017901001';

        $data = array();

        $task_id = 3;
        if($task_id == 0){
            $retJson['status'] = false;
            $retJson['msg'] = '当前无签到任务';
            return json($retJson);
        }else{
            $baseModel = new BaseModel;
            $class_id = 2017310201;

            $signList = Db::view('SignRecord','stu_id')
                ->view('YibanBaseInfo','name,number','YibanBaseInfo.number = SignRecord.stu_id')
                ->where('task_id',$task_id)
                ->where('class',$class_id)
                ->field('number,name')
                ->select();

            //班级已签人数
            // $data['sign_list'] = $signList;
            $data['sign_count'] = count($signList);

            //班级总人数
            $classCount = $baseModel->getClassStuCount($class_id);
            $data['class_stu_num'] = $classCount;

            $classList = Db::table('dp_sign_student')
                ->where('class',$class_id)
                ->field('number,name')
                ->select();

            $notSignList = array();

            foreach ($classList as $key => $value) {
                foreach ($signList as $k => $v) {
                    
                    if($value['number'] == $v['number']){
                        unset($classList[$key]);
                    }
                }

            }
            //此时经过unset掉已经签到的学号，$classList表示未签到的名单
            $data['class_id'] = $class_id;
            $data['not_sign_list'] = $classList;
            $data['not_sign_count'] = count($classList);

            return json($data);
        }

    }



}
