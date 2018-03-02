<?php
namespace app\sign\admin;

use app\admin\controller\Admin;
use think\Db;

/**
 * 晚点名管理后台
 * @package app\sign\admin
 */
class Amap extends Admin
{
	public function index(){

		$sign_data = Db::name('sign_record')->field('longitude,latitude')->select();
		//dump($sign_data);
		$this->assign('sign_data',$sign_data);
		return $this->fetch(); // 渲染模板
	}
}