<?php
namespace app\yiban\home;

use think\Log;
use think\Db;
use think\Session;

use app\index\controller\Home;


use app\yiban\model\BaseInfo as BaseModel;

/**
 * 易班签到控制器
 * @package app\yiban\home
 */

class Sign extends Api
{
    const SCHOOL_AREA = array(
        array( 'y' => 34.376459,'x' => 108.912002),
        array( 'y' => 34.370079,'x' => 108.914695),
        array( 'y' => 34.365792,'x' => 108.898129),
        array( 'y' => 34.370743,'x' => 108.890995),
        array( 'y' => 34.376459,'x' => 108.912002),
    );
    public function _initialize()
    {

    	parent::_initialize();
    }

  	public function init(){

  		$checkData = $this->checkParams();
  		if($checkData['status'] !== true){
  			return json($checkData);
  		}

     $stuInfo = $this->getStudentInfo($this->token);

      if($stuInfo != false){
          $model = new BaseModel;
          $stuBaseInfo = $model->getBaseInfoById($stuInfo->yb_studentid);

          $this->initData['status'] = 'true';
          $personalInitData['yb_realname'] = $stuInfo->yb_realname;
          $personalInitData['yb_id'] = $stuInfo->yb_userid;
          $personalInitData['stu_id'] = $stuInfo->yb_studentid;

          $personalInitData['head_img'] = $stuInfo->yb_userhead;
          $personalInitData['sign_status'] = $this->getUserSignStatus($personalInitData['stu_id']);

          if($stuBaseInfo['major']){
              $personalInitData['major'] = $stuBaseInfo['major'];
          }
          if($stuBaseInfo['college']){
              $personalInitData['college'] = $stuBaseInfo['college'];
          }

          $this->initData['personal'] = $personalInitData;
          $this->initData['public'] = $this->getSign($personalInitData['stu_id']);
          return json($this->initData);
      }else{
          $data['status'] = 'error';
          $data['code'] = '0x2004';
          $data['info'] = '无法获取易班信息，可能是未通过校方认证';
          return json($data);
      }

  }

    public function submit(){
      $token = $this->getToken($this->verifyRequest);

      //申请校级权限后打开
      $stu_id = $this->getStudentId($token);

    	$timestamp = input('post.noncestr');

    	$latitude = $this->checkLocationValid(input('post.latitude','0'));
    	$longitude = $this->checkLocationValid(input('post.longitude','0'));

    	if(!$stu_id||!$timestamp||!$latitude||!$latitude){
    		//请求数据不合法
    		$data['status'] = 'error';
    		$data['code'] = '0x2001';
    		$data['info'] = '定位参数非法';
    		return json($data);
    	}

        //记录签到数据，若重新签到，最少间隔1分钟。
        $time = time();
        if($time - $this->getUserSignTimeStamp($stu_id) < 60 ){
            $data['status'] = 'error';
            $data['code'] = '0x2002';
            $data['info'] = '已经签到，若要重签，请1分钟后再试';
        }else{
          //提交数据判断，仅未签（正常签到、补签） 或 定位不在学校签(重签) 可以正常签到。
             if(input('post.status') == 2||  input('post.status') == 0){
               $taskId = $this->getSignTaskId($stu_id);
               if($this->recordSignData($stu_id,$latitude,$longitude,$taskId)){
                   $data['status'] = 'success';
                   $data['info'] = '签到成功';
               }else{
                   $data['status'] = 'error';
                   $data['info'] = '签到失败';
                   $data['code'] = '0x2003';
               }else {
                 $data['status'] = 'error';
                 $data['code'] = '0x2002';
                 $data['info'] = '已经签到！';
               }
             }
        }
        return json($data);
    }

    /*
    * 获取签到率，分别有学院签到率，年级签到率，班级签到率
    */
    public function getSignRate(){
        //1.班级当前签到率
        $stu_id = input('get.stu_id');
        $stu_id = '2017901001';

        $task_id = $this->getSignTaskId($stu_id);
        $class_id = $this->getClassId($stu_id);

        $signCount = Db::view('SignRecord','id,task_id,stu_id')
            ->view('SignStudent','name,class,number,admin_id','SignStudent.number = SignRecord.stu_id')
            ->where('task_id',$task_id)
            ->where('class',$class_id)
            ->count();

        $numberOfClass = Db::table('dp_sign_student')
            ->where('class',$class_id)
            ->count();

        $classSignRate = round($signCount / $numberOfClass * 100,2);

        $data['status'] = 'success';
        $data['info'] = $classSignRate.'%';
        return json($data);
    }

    /*
    * 获取班级未签到名单，以$task_id为单位统计
    */
    public function getNotSignList(){
        $stu_id = input('get.stu_id');
        // $stu_id = '2017901001';

        $data = array();

        $task_id = $this->getSignTaskId($stu_id);
        if($task_id == 0){
            $retJson['status'] = false;
            $retJson['msg'] = '当前无签到任务';
            return json($retJson);
        }else{
            $baseModel = new BaseModel;
            $class_id = $baseModel->getClassId($stu_id);

            $signList = Db::view('SignRecord','stu_id')
                ->view('SignStudent','name,number','SignStudent.number = SignRecord.stu_id')
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

    public function notice(){
        $noticePage = input('post.p') ? input('post.p') : 1 ;

        $data = array();
        $notice = $this->getSignNotice('',$noticePage);

        if($notice){

            $data['status'] = true;
            $data['info'] = $notice;
        }

        return json($data);

    }

    public function me(){

        $token = $this->getToken($this->verifyRequest);
        //申请校级权限后打开
        $stuInfo = $this->getStudentInfo($token);
        if($stuInfo == false){
            $data['msg'] = '请求参数非法';
            $data['code'] = '0x4001';
            return json($data);
        }

        $data['stu_id'] = $stuInfo->yb_studentid;
        $data['name'] = $stuInfo->yb_realname;
        $data['yb_money'] = $stuInfo->yb_money;
        $data['head_img'] = $stuInfo->yb_userhead;
        //这里有错误，未做完
        $data['college'] = '信息工程学院';
        $data['major'] = '计算机类';

        $data['total_sign_count'] = '未统计';
        $data['total_sign_rate'] = '100%';
        $data['total_sign_rank'] = '未统计';
        $data['not_sign_count'] = '未统计';

        $tmp[0]['task_title'] = '尚未统计';
        $tmp[0]['task_time'] = '2018-01-01 17:00-19:00';

        $data['not_sign_list'] = $tmp;
        return json($data);
    }

    private function checkLocationValid($location){
    	//if 不合法 return false
    	return $location;
    }

    /*
    * 记录签到数据
    * params $stu_id,$latitude,$longitude,$task_id
    * return bool(true or false)
    */
    private function recordSignData($stu_id,$latitude,$longitude,$task_id){

        $point['x'] = $latitude;
        $point['y'] = $longitude;
        //bool(true or false)
        $atSchool = $this->isPointInPolygon(self::SCHOOL_AREA,$point);
        $data = [
            'stu_id' => $stu_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'task_id' => $task_id,
            'at_school'=> $atSchool
        ];
        if(Db::table('dp_sign_record')->insert($data)){
            return true;
        }else{
            return false;
        }
    }

    /*
    * 获取个人签到状态，是否签到
    * @return 0=未签 1=已签 -1=不存在点名任务
    */
    private function getUserSignStatus($stu_id){
        //1.当前时间是否存在点名任务，如果不存在，则不用签 status = -1;
        //2.当前时间存在点名任务，查看是否有签到记录
        //3.判断签到记录里的at_school如果是0则 status = 2表示可以重签，如果是1则不可以重签

        $task = $this->getSignTask($stu_id);
        if($task['task_status'] == 0){
            $status = -1;
        }else{
            $signRecord = Db::table('dp_sign_record')
                ->where('task_id',$task['id'])
                ->where('stu_id',$stu_id)
                ->order('id DESC')
                ->find();
            if(empty($signRecord)){
              $status = 0;
            }elseif($signRecord['at_school'] == 1){
              $status = 1;
            }elseif($signRecord['at_school'] == 0){
              //可以重新签
              $status = 2;
            }
        }
        return $status;
    }

    /*
    * 获取个人当前任务下上一次签到时间戳
    * @return timestamp
    */
    private function getUserSignTimeStamp($stu_id){
        $signRecord = 0;
        $task = $this->getSignTask($stu_id);

        if(isset($task['id'])){
            $signRecord = Db::table('dp_sign_record')
                ->where('task_id',$task['id'])
                ->where('stu_id',$stu_id)
                ->order('id DESC')
                ->value('timestamp');
            if($signRecord){
                $signRecord = strtotime($signRecord);
            }
        }

        return $signRecord;
    }

    /*
    * 获取当前点名任务及对应通知
    */

    private function getSign($stu_id){
        $signData = array();
        $sign = $this->getSignTask($stu_id);
        if($sign['task_status'] == 0){
            $signData = $sign;
        }else{
            $signData['task_id'] = $sign['id'];
            $signData['task_name'] = $sign['title'];
            $signData['start_time'] = date("m-d H:i",$sign['start_time']);
            $signData['end_time'] = date("m-d H:i",$sign['end_time']);
            $signData['notice'] = $this->getSignNotice($sign['id']);
            $signData['sms_verify'] = $sign['sms_verify'];
            $signData['task_status'] = $sign['task_status'];
        }

        return $signData;
    }

    /*
    * 2017-10-16 0:38 Yang
    * method 获取当前点名任务
    * params $stu_id: 学生学号
    * return $task: 点名人数数组（title,start_time,end_time,adminid,sort,status）
    */
    private function getSignTask($stu_id,$last = false){
        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $adminId = $this->getStuAdminId($stu_id);
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
              $task['msg'] = '正在进行签到';
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
                //即点名未开始(预告)
                $task['task_status'] = 2;
                $task['msg'] = '即将开始签到';
              }
            }
        }
        return $task;
    }

    /*
    * 2017-10-16 0:38 Yang
    * method 获取当前点名任务ID，若不存在则返回0
    * params $stu_id: 学生学号
    * return $task: 当前点名ID
    */
    private function getSignTaskId($stu_id){
        $task = $this->getSignTask($stu_id);

        return ($task['task_status'] == 0 ) ? 0 : $task['id'];
    }

    /*
    * 2017-10-16 0:38 Yang
    * method 获取当前时间上一次点名任务，截止当填晚上0:00:00，用来判断是否需要补签
    * params
    * return
    */
    private function getLastSignTask($stu_id){
        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $adminId = $this->getStuAdminId($stu_id);

        //若当前存在任务，则返回last_task_id = 0
        if($this->getSignTaskId($stu_id)){
            return 0;
        }else{
            $task = Db::table('dp_sign_task')
                ->where('end_time','<',$todayEndTime)
                ->where('end_time','>',$todayStartTime)
                ->order('start_time Desc,id DESC')
                ->find();
            return $task;
        }

    }

    /*
    * 用来判断当天是否有已经过去的签到任务
    */
    private function isHavePastTask($stu_id){
      $time = time();
      $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
      $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
      $adminId = $this->getStuAdminId($stu_id);
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
            ->where('at_school',1) //找出有效的签到，如果没有需要补签
            ->find();
        //dump($signRecord);
        if(empty($signRecord)){

          //dump($task);
          return $task;
        }else{
          //不存在需要补签的任务;
          return true;
        }
      }
    }

    /*
    * 获取当前点名对应通知
    * @params $sign_id(任务ID)
    */
    private function getSignNotice($sign_id,$page = 1){

        if(!empty($sign_id)){
            $signNotice = Db::table('dp_sign_notice')
                ->where('task_id',$sign_id)
                ->where('status',1)
                ->order('sort DESC,id DESC')
                ->field('id,title,notice,timestamp')
                ->select();
        }else{
            $notice = Db::table('dp_sign_notice')
                ->where('status',1)
                ->order('sort DESC,id DESC')
                ->page($page,10)
                ->field('id,title,notice,timestamp')
                ->select();

            $totalPage = (Db::table('dp_sign_notice')
                ->where('status',1)
                ->count()) / 10 ;

            $signNotice['notice'] = $notice;
            $signNotice['now_page'] = $page;
            $signNotice['total_page'] = (int)ceil($totalPage);
            $signNotice['rows_page'] = 10;
        }

        return $signNotice;
    }

    /*
    * 根据学号获取管理员（辅导员）ID
    */
    private function getStuAdminId($stu_id){
        $adminid = 1;
        return $adminid;
    }

    private function getClassId($stu_id){
        $class_id = Db::table('dp_sign_student')
            ->where('number',$stu_id)
            ->value('class');
        return $class_id;
    }

    /**
    * 射线法实现判断定位点是否在指定多边形区域内
    * @param $polygon:为一群点的数组 $lnglat:传入的点，此处用经纬度，例如：
    * $polygon = array(
    *    array(
    *        "lat" => 31.027666666667,
    *        "lng" => 121.42277777778
    *    ),
    *    array(
    *        "lat" => 31.016361111111,
    *        "lng" => 121.42797222222
    *    ),
    *    array(
    *        "lat" => 31.023666666667,
    *        "lng" => 121.45088888889
    *    ),
    *    array(
    *        "lat" => 31.035027777778,
    *        "lng" => 121.44575
    *    )
    * );
    * $lnglat = array(
    *     "lat" => 31.037666666667,
    *     "lng" => 121.43277777778
    * );
    */
    private function isPointInPolygon($polygon,$lnglat){
        $count = count($polygon);
        $px = $lnglat['x'];
        $py = $lnglat['y'];
    // echo $count.' '.$px.' '.$py;
        $flag = false;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i, $i++) {
            $sy = $polygon[$i]['y'];
            $sx = $polygon[$i]['x'];
            $ty = $polygon[$j]['y'];
            $tx = $polygon[$j]['x'];

            if ($px == $sx && $py == $sy || $px == $tx && $py == $ty){
                return true;
            }

            if ($sy < $py && $ty >= $py || $sy >= $py && $ty < $py) {
                $x = $sx + ($py - $sy) * ($tx - $sx) / ($ty - $sy);
                if ($x == $px){
                    return true;
                }

                if ($x > $px){
                    $flag = !$flag;
                }
            }
        }
        return $flag;
    }

}
