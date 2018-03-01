<?php
namespace app\yiban\home;

use think\Log;
use think\Db;
use think\Session;
use yiban\YBOpenApi;

/**
 * 易班签到控制器
 * @package app\yiban\home
 */
class Sign extends Api
{

	public function _initialize()
    {
    	parent::_initialize();
    }

	public function init(){
		$checkData = $this->checkParams();
		if($checkData['status'] !== true){
			return json($checkData);
		}

		$this->initData['status'] = 'true';
		$this->initData['info'] = 'CHD-Yi tomato';

		return json($this->initData);
	}