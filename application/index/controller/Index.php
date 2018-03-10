<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\index\controller;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;
use app\yiban\model\BaseInfo as BaseModel;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Home
{
      public function getSignTask(){
          $adminId = 1;
          $stu_id = "2017904088";
          $time = time();
          $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
          $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

          //先来判断今天开始到此刻有没有未签到的点名
          $result = $this -> isHavePastTask($stu_id);
          if ($result !== true) {
            $task =  $result;
            $task['task_status'] = 3;
            $task['msg'] = '你有任务未签到，请进行补签';
          }else{
            //1.当前时间在任务开始-结束区间内
            $task = Db::table('dp_sign_task')
                ->where('start_time','<',$time)
                ->where('end_time','>',$time)
                ->where('status',1)
                ->where('adminid',$adminId)
                //排序1：sort重要性排序，排序2：选择开始时间晚的
                ->order('sort DESC,start_time DESC')
                ->find();
            if($task){
                $task['task_status'] = 1;
                $task['msg'] = '正在进签到';
            }else{
                //2.当前时间不在任务开始-区间内,寻找接下来还有没有任务
                $task = Db::table('dp_sign_task')
                    ->where('start_time','>',$time)
                    ->where('end_time','<',$todayEndTime)
                    ->order('start_time ASC,id ASC')
                    ->find();
                if (empty($task)) {
                  $task['task_status'] = 0;
                  $task['msg'] = '当前无签到任务';
                }else {
                  echo "下次点名还没到，进行预告";
                  //即点名未开始(预告)
                  $task['task_status'] = 2;
                  $task['msg'] = '即将开始签到';
                }
              }
          }
          dump($task);
      }
      /*
      * 用来获取距离当前时间最近的下次当天签到任务
      */
      public function getTodayNextTask(){
        $stu_id = "2017902148";
        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

        $task = Db::table('dp_sign_task')
            ->where('start_time','>',$time)
            ->where('end_time','<',$todayEndTime)
            ->order('start_time ASC,id ASC')
            ->find();
        if (empty($task)) {
          echo "当天没有后续的签到任务了";
        }else {
          echo "下次点名还没到，进行预告";
        }
      }

      /*
      * 用来判断当天是否有已经过去的签到任务
      */
      public function isHavePastTask($stu_id){
        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));

        $task = Db::table('dp_sign_task')
            ->where('end_time','<',$time)
            ->where('start_time','>',$todayStartTime)
            ->order('start_time Desc,id DESC')
            ->find();
        //dump($task);
        if(empty($task)){
            //当天到此刻还没有签到任务
            return true;
        }else {
          //要判断该学生是否签到
          $signRecord = Db::table('dp_sign_record')
              ->where('task_id',$task['id'])
              ->where('stu_id',$stu_id)
              ->find();
          //dump($signRecord);
          if(empty($signRecord)){
            //echo "您需要先补签";
            //dump($task);
            return $task;
          }else{
            //不存在需要补签的任务;
            return true;
          }
        }
      }

}
