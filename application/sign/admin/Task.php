<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;
use app\user\model\Role as RoleModel;


/**
 * 晚点名管理后台
 * @package app\sign\admin
 */
class Task extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }
    //任务列表
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
                }],
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

}

   