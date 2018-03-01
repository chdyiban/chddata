<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 本科教务
 * @package app\yiban\bkjw
 */
class Sports extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    public function index(){
    	$order = $this->getOrder();
    	$map = $this->getMap();

    	$data_list = Db::name('sports_index')->where($map)->order($order)->paginate();

        return ZBuilder::make('table')
            ->setTableName('sports_index')
            ->hideCheckbox()
			->addColumns([
				['name', '姓名','', '', '', 'text-center'],

			])
        	->setPageTitle('报名详情')
        	// ->setSearch(['name' => '姓名','number'=>'学号'])
        	->setRowList($data_list) // 设置表格数据
        	// ->addOrder('id') // 添加排序
        	// ->addFilter('college,class_name')
        	->fetch();
    }
}