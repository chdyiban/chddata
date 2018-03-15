<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;

/**
 * 易班签到：通知管理
 * @author yongyijiu, rewirte by Yang 2018.3.8
 */
class Notice extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    /**
    * 通知列表
    * @author yongyijiu
    * noticeList->list
    * 2018 3.8 21:28 yang
    *后期仍需加上一些验证
    */
    public function noticeList(){
      $order = $this->getOrder();
    	$map = $this->getMap();
      //注意这里sign_task里的id必须换个别名，否则下面的编辑界面无法实现功能.
    	$data_list = Db::view('sign_notice')
                    ->view('sign_task',['title'=>'task_title','id'=>'taskId'],
                        'sign_notice.task_id=sign_task.id')
                    ->where($map)
                    ->order($order)
                    ->paginate();

      //得出选项的数组，在新增通知界面使用
      $task_list = Db::name('sign_task')->Field('id,title')->select();
      $select_list = array();

      foreach ($task_list as $value) {
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
        ['hidden','id'],
        ['text', 'title', '标题', '必填'],
        ['textarea', 'notice', '通知内容', '发布的通知的具体内容'],
      ];

      return ZBuilder::make('table')
        ->hideCheckbox()
        ->addColumns([
          ['task_title', '晚点名事项',],
    			['title', '通知标题','text.edit', '', '', 'text-center'],
    			['notice', '通知内容', 'text.edit', '', '', 'text-center'],
    			['timestamp', '发布时间'],
    			['right_button', '操作', 'btn']
    		])
        ->autoAdd($fields_add, 'sign_notice', '', 'timestamp|Y-m-d H:i:m') // 添加新增按钮
        ->autoEdit($fields_edit, 'sign_notice','','timestamp|Y-m-d H:i:m') // 添加编辑按钮
        ->addRightButton('delete') // 添加删除按钮
        ->addOrder(['timestamp']) // 添加排序
        ->addFilter(['task_title' => 'sign_task.title'])
  			->setRowList($data_list) // 设置表格数据
  			->setPageTitle('通知列表')
  			->fetch();
      }

}
