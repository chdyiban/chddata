<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 签到模块后台
 * @author yongyijiu, rewirte by Yang 2018.3.3
 * Rewirte Steps:
 * 1.原sign模块去除无用功能，整体迁移至yiban下
 * 2.前后台公用方法写入yiban/common.php下
 * 3.数据库视图重写为tp的视图模型
 * @package app\yiban\bkjw
 */
class Sign extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }


    /**
    * 通知列表，原路径sign/admin/notice.php
    * @author yongyijiu
    */
    public function noticeList(){

    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$data_list = Db::name('sign_notice')->where($map)->order($order)->paginate();
        $task = Db::name('sign_task')->select();
        //得出选项的数组
        $select_list = array();
        foreach ($task as $value) {
            $select_list[$value['id']] = $value['title'];
        }
		// 定义新增页面的字段
		$fields_add = [
			['text', 'title', '标题', '必填'],
			['textarea', 'notice', '通知内容', '发布的通知的具体内容'],
            ['select', 'task_id', '晚点名', '通知发布所针对的晚点名',$select_list],
            ['radio', 'status', '状态', '', ['禁用', '发布'], 1],	
        ];
        //定义编辑页面的字段
        $fields_edit = [
    	    ['hidden', 'id'],
    	    ['text', 'title', '标题', '必填'],
    	    ['textarea', 'notice', '通知内容', '发布的通知的具体内容']
	
    	];

 		return ZBuilder::make('table')
 				->addColumns([
					['id', 'ID','', '', '', 'text-center'],
					['title', '通知标题','text.edit', '', '', 'text-center'],
					['notice', '通知内容', 'text.edit', '', '', 'text-center'],
					['timestamp', '发布时间'],
					['task_id', '晚点名事项','callback',function($value){
        				$title = DB::name('sign_task')->where('id = '.$value)->value('title');
        				return $title;
           				}
           			],
					['right_button', '操作', 'btn']
				])

 				->autoAdd($fields_add, 'sign_notice', '', 'timestamp|Y-m-d', '', true) // 添加新增按钮
                ->autoEdit($fields_edit, 'sign_notice','','timestamp|Y-m-d') // 添加编辑按钮
                ->addRightButton('delete') // 添加删除按钮
                ->addValidate('Config', 'title，notice') // 添加快捷编辑的验证器
                ->addOrder(['id','timestamp']) // 添加排序
                ->addFilter(['id','task_id']) // 添加筛选
                ->addTimeFilter('timestamp') // 添加时间段筛选
 				->setRowList($data_list) // 设置表格数据
 				->setPageTitle('通知列表')
 				->fetch();
    }

    /**
    * 任务列表，原路径 sign/admin/task.php
    * @author yongyijiu
    * 
    */
    public function taskList(){

    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$data_list = Db::name('sign_task')->where($map)->order($order)->paginate();

        // 定义新增页面的字段
        $fields_add = [
            ['text', 'title', '晚点名标题', '必填'],
            ['datetime','start_time','开始时间'],
            ['datetime','end_time','结束时间'],
            ['radio', 'status', '状态', '', ['禁用', '发布'], 1], 
            ['hidden','adminid',UID],
        ];

        // 定义编辑页面的字段
         $fields_edit = [
            ['hidden', 'id'],
            ['text', 'title', '晚点名标题', '必填'],
            ['datetime','start_time','开始时间'],
            ['datetime','end_time','结束时间'],
            ['hidden','adminid',UID],
            ['hidden','status',1]
        ];

 		return ZBuilder::make('table')
 				->addColumns([
				['id', 'ID','', '', '', 'text-center'],
				['title', '任务标题','text.edit', '', '', 'text-center'],
				['start_time', '开始时间', 'datetime.edit', '', '', 'text-center'],
				['end_time', '结束时间', 'datetime.edit', '', '', 'text-center'],
                ['adminid', '发布者','callback',function($value){
                	$name = DB::name('admin_user')->where('id = '.$value)->value('nickname');
                    	return $name;
                	}
                ],
				['right_button', '操作', 'btn']
			])
                ->autoAdd($fields_add, 'sign_task','','start_time,end_time') // 添加新增按钮
                ->autoEdit($fields_edit, 'sign_task','','start_time,end_time') // 添加编辑按钮
                ->addRightButton('delete') // 添加删除按钮
                ->addOrder('id') // 添加排序
 				->setRowList($data_list) // 设置表格数据
 				->setPageTitle('正常任务')
 				->fetch();
    }

    //签到信息列表
    public function signList($group = 'tab1'){

    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$data_list = Db::name('sign_list')->where($map)->order($order)->paginate();

        $list_tab = [
            'tab1' => ['title' => '签到信息列表', 'url' => url('signList', ['group' => 'tab1'])],
            'tab2' => ['title' => '热力图统计', 'url' => url('signList', ['group' => 'tab2'])]
        ];

        switch ($group) {
        case 'tab1':
           return ZBuilder::make('table')
                ->addColumns([
                ['id', 'ID','', '', '', 'text-center'],
                ['name', '姓名','', '', '', 'text-center'],
                ['class', '班级','', '', '', 'text-center'],
                ['number', '学号','', '', '', 'text-center'],
                ['college', '学院','', '', '', 'text-center'],
                ['latitude', '经度', '', '', '', 'text-center'],
                ['longitude', '纬度', '', '', '', 'text-center'],
                ['at_school', '位置', '', '', '', 'text-center'],
                ['timestamp', '签到时间'],
                ['task_id', '晚点名事项','callback',function($value){
                	$title = DB::name('sign_task')->where('id = '.$value)->value('title');
                    return $title;
                	}
                ],
                ['right_button', '操作', 'btn']
            ])
                ->addRightButton('delete') // 添加删除按钮
                ->addValidate('Config', 'title，notice') // 添加快捷编辑的验证器
                ->addOrder(['id','timestamp']) // 添加排序
                ->addTimeFilter('timestamp') // 添加时间段筛选
                ->setRowList($data_list) // 设置表格数据
                ->setTabNav($list_tab,  $group)
                ->fetch();
            break;
        case 'tab2':
            $data_location = DB::name('sign_record')->field("latitude")->select();       
            return parent::fetch('map',['data' => $data_location]);
            
            break;
          }
 		
    }

    //签到率统计
    public function signCount($group = 'tab1'){

        $order = $this->getOrder();
        $map = $this->getMap();
        //获取班级的列表信息同时获取一个学号用来回调得到班级号
        $data_class_list = Db::name('class_list')->where($map)->order($order)->paginate();
        $list_tab = [
            'tab1' => ['title' => '班级签到率', 'url' => url('signCount', ['group' => 'tab1'])],
            'tab2' => ['title' => '学院签到率', 'url' => url('signCount', ['group' => 'tab2'])],
            'tab3' => ['title' => '年级签到率', 'url' => url('signCount', ['group' => 'tab3'])],
        ];


        switch ($group) {
        case 'tab1':
            return ZBuilder::make('table')
                ->addColumns([                
                ['class', '签到率','callback',function($value){
                    return $this->getSignRateByClassId($value);
                }],
                ['number','班级','callback',function($value){
                    return $this->getClassId($value);
                }],
                ['major','专业'],
                ['college','学院'],
              
            ])
                ->addOrder('class')
                ->setTabNav($list_tab,  $group)
                ->setRowList($data_class_list) // 设置表格数据
                ->fetch();
            break;
        case 'tab2':
            return ZBuilder::make('table')
                ->addColumns([
                ['class', '学院','', '', '', 'text-center'],
                ['signRate', '签到率','', '', '', 'text-center'],
              
            ])
                ->setTabNav($list_tab,  $group)
                ->fetch();
            break;
        case 'tab3':
            return ZBuilder::make('table')
                ->addColumns([
                ['class', '年级','', '', '', 'text-center'],
                ['signRate', '签到率','', '', '', 'text-center'],
              
            ])
                ->setTabNav($list_tab,  $group)
                ->fetch();
            break;
          }
    }

    //特殊情况列表
    public function signSpecial(){
        $order = $this->getOrder();
        $map = $this->getMap();
        $data_list = Db::name('class_list')->where($map)->order($order)->paginate();

        return ZBuilder::make('table')
                ->addColumns([                
               
                ['number','班级','callback',function($value){
                    return $this->getClassId($value);
                }],
                ['major','专业'],
                ['college','学院'],
               ['class', '未签到人数','callback',function($value){
                    return $this->getNotSignListByClassId($value);
                }],
            ])
                ->addOrder('class')
                ->setRowList($data_list) // 设置表格数据       
                ->setPageTitle('特殊情况列表')
                ->fetch();
    }


     /*
    * 获取签到率，分别有学院签到率，年级签到率，班级签到率
    */
    public function getSignRateByClassId($class_id){
        //获取任务id，为3？？
        $task_id = $this->getSignTaskId();
        //dump($task_id);
        //统计一个班的签到人数
        $signCount = Db::view('SignRecord','id,task_id,stu_id')
            ->view('YibanBaseInfo','name,class,number,admin_id','YibanBaseInfo.number = SignRecord.stu_id')
            ->where('task_id',$task_id)
            ->where('class',$class_id)
            ->count();
        //获取班级总人数
        $numberOfClass = Db::table('dp_yiban_base_info')
            ->where('class',$class_id)
            ->count();

        $classSignRate = round($signCount / $numberOfClass * 100,2);


        return $classSignRate.'%';
    }

    /*
    * 获取班级未签到名单，以$task_id为单位统计
    */
    public function getNotSignListByClassId($class_id){
        $data = array();
        $task_id = $this->getSignTaskId();
        if($task_id == 0){
            $retJson['status'] = false;
            $retJson['msg'] = '当前无签到任务';
            return json($retJson);
        }else{
            $baseModel = new BaseModel;
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

            //return json($data);
            return count($classList);
        }

    }
    /*
    * 2017-10-16 0:38 Yang
    * method 获取当前点名任务
    * params $stu_id: 学生学号
    * return $task: 点名人数数组（title,start_time,end_time,adminid,sort,status）
    */
    private function getSignTask($last = false){

        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        /*
        
         */
        //$adminId = $this->getStuAdminId($stu_id);
        $adminId = 1;
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
            $task['msg'] = '当前时间存在签到任务';
        }else{
            //2.当前时间不在任务开始-区间内
            $task = Db::table('dp_sign_task')
                ->where('end_time','<',$todayEndTime)
                ->where('start_time','>',$todayStartTime)
                ->order('sort DESC,start_time DESC')
                ->find();
            if(empty($task)){
                $task['task_status'] = 0;
                $task['msg'] = '当前无签到任务';
            }elseif($time < $task['start_time']){
                //当天存在任务，且当前时间小于start_time，即点名未开始(预告)
                $task['task_status'] = 2;
                $task['msg'] = '即将开始签到';
            }elseif($time >= $task['end_time']){
                //当前存在任务，且当前时间大于end_time，即点名已经结束（补签）
                $task['task_status'] = 3;
                $task['msg'] = '签到已经结束';
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
    private function getSignTaskId(){
        $task = $this->getSignTask();

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
    * 根据学号获取管理员（辅导员）ID
    */
    private function getStuAdminId(){
        $adminid = 1;
        return $adminid;
    }
    private function getClassId($stu_id){
        $class_id = Db::table('dp_yiban_base_info')
            ->where('number',$stu_id)
            ->value('class');
        return $class_id;
    }

}