<?php

namespace app\yiban\model;

use think\Model;

class Sign extends Model
{
	protected $table = 'dp_yiban_base_info';


	public function getSignRateList($task_id,$map,$order){
		$result =  $this->where($map)->order($order)->group('class')->paginate();
		return $result->toArray()['data'];
	}

}