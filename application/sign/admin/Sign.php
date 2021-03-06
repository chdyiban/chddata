<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;
use app\yiban\model\BaseInfo as BaseModel;

/**
 * 签到模块后台
 * @author yongyijiu, rewirte by Yang 2018.3.3
 */
class Sign extends Admin
{
    public function config(){
      return $this->moduleConfig();
    }
    //签到信息列表
    public function signList($group = 'tab1'){
      $order = $this->getOrder();
      $map = $this->getMap();
      $taskId = $this -> getLastSignTask()['id'];
      //用来获取表头下拉菜单的默认值，默认值设为上次晚点名
      //存在一个问题，如果当前正在晚点名，可能希望看到的默认是当前的晚点名而非上次晚点名
      if (empty($map) || !isset($map['task_id'])) {
        $map['task_id'] = $taskId;
      }
      //用视图通过stu_id获取学生信息，task_id获取点名信息
      //存在一个问题，dp_yiban_base_info中，不存在admin_id，无法用视图选出每个管理员管理的学生
      //添加了admin_id
      $data_list = Db::connect('chd_config')
            ->view('dp_sign_record')
            ->view('dp_sign_task','id,title,adminid','dp_sign_record.task_id=dp_sign_task.id')
            ->view('chd_stu_detail','XH,XM,BJDM,MZDM,YXDM,ZYDM','dp_sign_record.stu_id=chd_stu_detail.XH')
            ->view('chd_dict_nation','MZDM,MZMC','chd_stu_detail.MZDM = chd_dict_nation.MZDM')
            ->view('chd_dict_major','ZYDM,ZYMC','chd_stu_detail.ZYDM = chd_dict_major.ZYDM')
            ->view('chd_dict_college','YXDM,YXMC,YXJC','chd_stu_detail.YXDM = chd_dict_college.YXDM')
            ->view('chd_dict_fdy','BJDM,admin_id','chd_stu_detail.BJDM = chd_dict_fdy.BJDM')
            ->where($map)
            ->order($order)
            ->paginate();
      $list_tab = [
        'tab1' => ['title' => '签到信息列表', 'url' => url('signList', ['group' => 'tab1'])],
        'tab2' => ['title' => '热力图统计', 'url' => url('signList', ['group' => 'tab2'])]
      ];
      //获取上次的晚点名之前的所有点名下拉选项
      //注意这里要保证$task_id是自增的
      $sign_task_list = Db::name('sign_task')->where('id','<=',$taskId)->column('id,title');
      switch ($group) {
        case 'tab1':
        return ZBuilder::make('table')
        ->hideCheckbox()
        ->addColumns([
            ['XM', '姓名','', '', '', 'text-center'],
            ['BJDM', '班级','', '', '', 'text-center'],
            ['XH', '学号','', '', '', 'text-center'],
            ['YXJC', '学院','', '', '', 'text-center'],
            ['latitude', '经度', '', '', '', 'text-center'],
            ['longitude', '纬度', '', '', '', 'text-center'],
            ['at_school', '位置', '', '', '', 'text-center'],
            ['timestamp', '签到时间'],
            ['title', '晚点名事项'],
            ['right_button', '操作', 'btn']
          ])
          ->addRightButton('delete') // 添加删除按钮
          ->addOrder('timestamp') // 添加排序
          ->addTopSelect('task_id', '',$sign_task_list,$taskId)//添加顶部筛选
          ->setRowList($data_list) // 设置表格数据
          ->setTabNav($list_tab,  $group)
          ->fetch();
        break;
        case 'tab2':
        //传递task_id的问题
        $location_data = Db::name('sign_record')->field("latitude,longitude")->select();
          return view('map',['location_data' => $location_data]);
          break;
        }
      }
    //签到率统计
    //目前暂时无法根据签到率排序
    public function signRateCount($group = 'tab1'){
        $order = $this->getOrder();
        $map = $this->getMap();
        $taskId = $this -> getLastSignTask()['id'];
        //用来获取表头下拉菜单的默认值，默认值设为上次晚点名
        if (empty($map) || !isset($map['task_id'])) {
            $map['task_id'] = $taskId;
        }
        //获取上次之前所有的晚点名下拉选项
        $sign_task_list = Db::name('sign_task')->where('id','<=',$taskId)->column('id,title');
        //获取班级的列表信息同时获取一个学号用来回调得到班级号
        $data = Db::connect('chd_config')
            ->view('dp_sign_record')
            ->view('dp_sign_task','id,title,adminid','dp_sign_record.task_id=dp_sign_task.id')
            ->view('chd_stu_detail','XH,BJDM,YXDM,ZYDM','dp_sign_record.stu_id=chd_stu_detail.XH')
            ->view('chd_dict_nation','MZDM,MZMC','chd_stu_detail.MZDM = chd_dict_nation.MZDM')
            ->view('chd_dict_major','ZYDM,ZYMC','chd_stu_detail.ZYDM = chd_dict_major.ZYDM')
            ->view('chd_dict_college','YXDM,YXMC,YXJC','chd_stu_detail.YXDM = chd_dict_college.YXDM')
            ->view('chd_dict_fdy','BJDM,admin_id','chd_stu_detail.BJDM = chd_dict_fdy.BJDM')
            ->group('chd_stu_detail.BJDM')
            ->where($map)
            ->order($order)
            ->paginate();
        $list_tab = [
            'tab1' => ['title' => '班级签到率', 'url' => url('signCount', ['group' => 'tab1'])],
            'tab2' => ['title' => '学院签到率', 'url' => url('signCount', ['group' => 'tab2'])],
            'tab3' => ['title' => '年级签到率', 'url' => url('signCount', ['group' => 'tab3'])],
        ];
        switch ($group) {
            case 'tab1':
            return ZBuilder::make('table')
                ->hideCheckbox()
                ->addColumns([
                    ['BJDM', '签到率','callback',function($value, $data){
                    return $this->getSignRateByClassId($value, $data['id']);
                    },'__data__'],
                    ['XH','班级','callback',function($value){
                    return $this->getClassId($value);
                    }],
                    ['ZYMC','专业'],
                    ['YXMC','学院'],
                    ['title','晚点名'],
                ])
                ->addTopSelect('task_id', '',$sign_task_list,$taskId)//添加顶部筛选
                ->setTabNav($list_tab,  $group)
                ->setRowList($data) // 设置表格数据
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
    public function signSpecialList(){
        $order = $this->getOrder();
        $map = $this->getMap();
        $taskId = $this -> getLastSignTask()['id'];
        //用来获取表头下拉菜单的默认值，默认值设为上次晚点名
        if (empty($map) || !isset($map['task_id'])) {
            $map['task_id'] = $taskId;
        }

        //获取上次之前所有的晚点名下拉选项
        $sign_task_list = Db::name('sign_task')->where('id','<=',$taskId)->column('id,title');

        //这个语句可以找出所有的班级但是由于目前数据库中的数据有限所以多数班级筛选出task_id为空

        $data = Db::connect('chd_config')
            ->view('dp_sign_record')
            ->view('dp_sign_task','id,title,adminid','dp_sign_record.task_id=dp_sign_task.id')
            ->view('chd_stu_detail','XH,BJDM,YXDM,ZYDM','dp_sign_record.stu_id=chd_stu_detail.XH')
            ->view('chd_dict_nation','MZDM,MZMC','chd_stu_detail.MZDM = chd_dict_nation.MZDM')
            ->view('chd_dict_major','ZYDM,ZYMC','chd_stu_detail.ZYDM = chd_dict_major.ZYDM')
            ->view('chd_dict_college','YXDM,YXMC,YXJC','chd_stu_detail.YXDM = chd_dict_college.YXDM')
            ->view('chd_dict_fdy','BJDM,admin_id','chd_stu_detail.BJDM = chd_dict_fdy.BJDM')
            ->group('chd_stu_detail.BJDM')
            ->where($map)
            ->order($order)
            ->paginate();
        return ZBuilder::make('table')
            ->hideCheckbox()
            ->addColumns([
                ['XH','班级','callback',function($value){
                    return $this->getClassId($value);
                }],
                ['ZYMC','专业','', '', '', 'text-center'],
                ['YXJC','学院','', '', '', 'text-center'],
                ['BJDM','未签到人数','callback',function($value, $data){
                    $classList = $this -> getNotSignListByClassId($value, $data['id']);
                    return count($classList);
                },'__data__','', '', '', 'text-center'],

        ])
            ->addColumn('right_button', '查看详情', 'btn')
            ->addTopSelect('task_id', '',$sign_task_list,$taskId)//添加顶部筛选
            ->addRightButton('edit',['href' => url('signSpecialDetailList',['class_id' => '__BJDM__','task_id'=> '__task_id__']),'title' => '详情','icon'=>'fa fa-fw fa-th-list'],'')
            ->setRowList($data) // 设置表格数据
            ->setPageTitle('特殊情况列表')
            ->fetch();
    }
     /*
    * 获取签到率，分别有学院签到率，年级签到率，班级签到率
    */
    private function getSignRateByClassId($class_id, $task_id){

        //统计一个班的签到人数
        $signCount = Db::connect('chd_config')
            ->view('dp_sign_record')
            ->view('dp_sign_task','id,title,adminid','dp_sign_record.task_id=dp_sign_task.id')
            ->view('chd_stu_detail','XH,BJDM,YXDM,ZYDM','dp_sign_record.stu_id=chd_stu_detail.XH')
            ->view('chd_dict_fdy','BJDM,admin_id','chd_stu_detail.BJDM = chd_dict_fdy.BJDM')
            ->where('task_id',$task_id)
            ->where('BJDM',$class_id)
            ->count();
        //获取班级总人数
        $numberOfClass = Db::table('chd_stu_detail')
            ->where('BJDM',$class_id)
            ->count();

        $classSignRate = round($signCount / $numberOfClass * 100,2);


        return $classSignRate.'%';
    }

    //用来显示每个班级的没签到的人员的具体信息
    public function signSpecialDetailList($class_id, $task_id){
        $classList = $this -> getNotSignListByClassId($class_id, $task_id);
        return ZBuilder::make('table')
            ->hideCheckbox()
            ->addColumns([
            ['XH','学号','', '', '', 'text-center'],
            ['XM','姓名','', '', '', 'text-center'],
            ])
            ->setRowList($classList) // 设置表格数据
            ->setPageTitle('未签到人员名单')
            ->fetch();
    }
    /*
    * 获取班级未签到名单，以$task_id为单位统计
    */
    private function getNotSignListByClassId($class_id, $task_id){
        $data = array();
        $baseModel = new BaseModel;
        $signList = Db::connect('chd_config')
            ->view('dp_sign_record')
            ->view('dp_sign_task','id,title,adminid','dp_sign_record.task_id=dp_sign_task.id')
            ->view('chd_stu_detail','XH,BJDM,YXDM,ZYDM','dp_sign_record.stu_id=chd_stu_detail.XH')
            ->view('chd_dict_nation','MZDM,MZMC','chd_stu_detail.MZDM = chd_dict_nation.MZDM')
            ->view('chd_dict_fdy','BJDM,admin_id','chd_stu_detail.BJDM = chd_dict_fdy.BJDM')
            ->where('task_id',$task_id)
            ->where('BJDM',$class_id)
            ->field('XH,XM')
            ->select();

        //班级已签人数
        // $data['sign_list'] = $signList;
        $data['sign_count'] = count($signList);

        //班级总人数
        $classCount = $baseModel->getClassStuCount($class_id);
        $data['class_stu_num'] = $classCount;

        $classList = Db::table('chd_stu_detail')
            ->where('BJDM',$class_id)
            ->field('XH,XM,BJDM')
            ->select();



        foreach ($classList as $key => $value) {
            foreach ($signList as $k => $v) {

                if($value['XH'] == $v['XH']){
                    unset($classList[$key]);
                }
            }

        }
        return $classList;
    }
    /*
    * 2017-10-16 0:38 Yang
    * method 获取当前时间上一次点名任务，截止当填晚上0:00:00，用来判断是否需要补签
    * params
    * return 距离当前时间最近的已经结束的上次晚点名的信息
    */
    private function getLastSignTask(){
        $time = time();
        $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
        $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        //$adminId = $this->getStuAdminId();
        $adminId = 1;
        //获取距离当前时间最近的上一次已经结束的晚点名任务
        $task = Db::table('dp_sign_task')
            ->where('end_time','<',$time)
            ->order('start_time Desc,id DESC')
            ->find();
        return $task;
    }
    /*
    * 根据学号获取管理员（辅导员）ID
    */
    private function getStuAdminId(){
        $adminid = 1;
        return $adminid;
    }
    private function getClassId($stu_id){
        $class_id = Db::table('chd_stu_detail')
            ->where('XH',$stu_id)
            ->value('BJDM');
        return $class_id;
    }

}
