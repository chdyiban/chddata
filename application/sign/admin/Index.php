<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 晚点名管理后台
 * @package app\sign\admin
 */
class Index extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }
    //
    public function signList(){

    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$data_list = Db::name('sign_index')->where($map)->order($order)->paginate();

        return ZBuilder::make('table')
			->addColumns([
				['id', 'ID','', '', '', 'text-center'],
				['userid', '学生姓名','callback',function($value){
        			$name = DB::name('wx_user')->where('access_token',$value)->value('name');
        			return $name;
           		}],
				['signid', '点名任务ID','', '', '', 'text-center'],
				['latitude', '经度'],
				['longitude', '经度'],
				['timestamp', '上报时间'],
				['right_button', '操作', 'btn']
			])
        	->setPageTitle('点名数据')
        	->setSearch(['id' => 'ID', 'openid' => '学生姓名'])
        	->setRowList($data_list) // 设置表格数据
        	->addOrder(['id','openid','timestamp']) // 添加排序
        	->addFilter(['id','openid']) // 添加筛选
        	->addTimeFilter('timestamp') // 添加时间段筛选
        	->addRightButtons(['edit','delete'])
        	->fetch();

    }

}