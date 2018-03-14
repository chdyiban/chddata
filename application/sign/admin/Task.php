<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;
use think\Request;
use app\sign\model\SignTask as SignTaskModel;

/**
 * 易班签到：任务管理
 * @author yongyijiu, rewirte by Yang 2018.3.8
 */
class Task extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }
    //编辑页面
    public function taskEdit($id){
      return ZBuilder::make('form')
        ->setUrl(url('saveEditTask'))
        ->addFormItems([
            ['text', 'title', '晚点名标题', '必填'],
            ['datetime','start_time','开始时间','选择点名的开始时间'],
            ['datetime','end_time','结束时间','选择点名的结束时间'],
            ['hidden','adminid',UID],
            ['hidden','status',1]
       ])
       ->addHidden('id',$id)
        ->fetch();


    }
    //将编辑页面修改的数据写入数据库
    public function saveEditTask(Request $request)
    {

      $signTaskModel = new SignTaskModel;
      $editData = $request ->post();
      $res = $signTaskModel->saveEditTaskData($editData);
      // 更新数据
      if ($res !== 1) {
          $this->success('编辑失败', null, '_parent_reload');
      } else {
          $this->success('编辑成功', 'Task/taskList');
      }
    }

    public function add()
    {
      return ZBuilder::make('form')
        ->setUrl(url('saveAddTask'))
        ->addFormItems([
          ['text', 'title', '晚点名标题', '必填'],
          ['datetime','start_time','开始时间','选择点名的开始时间'],
          ['datetime','end_time','结束时间','选择点名的结束时间'],
          ['radio', 'status', '状态', '', ['禁用', '发布'], 1],
          ['hidden','adminid',UID]
       ])
        ->fetch();
    }
    //将新增页面的数据写入数据库
    public function saveAddTask(Request $request)
    {
      $signTaskModel = new SignTaskModel;
      $addData = $request ->post();
      $res = $signTaskModel->saveAddTaskData($addData);
      // 更新数据
      if ($res !== 1) {
          $this->success('编辑失败', null, '_parent_reload');
      } else {
          $this->success('编辑成功', 'Task/taskList');
      }
    }
    /**
    *2018.3.8 21:31
    * 任务列表，
    * @author yongyijiu
    *
    */
    public function taskList(){
      $order = $this->getOrder();
      $map = $this->getMap();
      $data_list = Db::name('sign_task')->where($map)->order($order)->paginate();
      return ZBuilder::make('table')
        ->hideCheckbox()
        ->addColumns([
          ['id', 'ID','', '', '', 'text-center'],
          ['title', '任务标题','text.edit', '', '', 'text-center'],
          ['start_time', '开始时间', 'datetime.edit', '', '', 'text-center'],
          ['end_time', '结束时间', 'datetime.edit', '', '', 'text-center'],
          ['adminid', '发布者','callback',function($value){
            $name = DB::name('admin_user')->where('id = '.$value)->value('nickname');
            return $name;
            }],
          ['right_button', '操作', 'btn']
        ])
        ->addTopButtons('add') // 批量添加顶部按钮
        ->addRightButton('edit', ['href' => url('taskEdit', ['id' => '__id__'])])
        ->addRightButton('delete') // 添加删除按钮
        ->addOrder('id') // 添加排序
        ->setRowList($data_list) // 设置表格数据
        ->setPageTitle('正常任务')
        ->fetch();
  }



}
