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
class Notice extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }
    //通知列表
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
           		}],
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

}