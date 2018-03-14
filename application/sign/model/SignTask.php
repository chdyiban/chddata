<?php
namespace app\sign\model;

use think\Model;

class SignTask extends Model
{
	protected $table = 'dp_sign_task';

	public function saveEditTaskData($value)
	{
		$value['end_time'] = strtotime($value['end_time']);
		$value['start_time'] = strtotime($value['start_time']);
		$res = $this->save([
		    'adminid'  => $value['adminid'],
		    'start_time' => $value['start_time'],
				'end_time' => $value['end_time'],
				'status' => $value['status'],
				'title' => $value['title'],
			],['id' => $value['id']]);
		return $res;
	}

	public function saveAddTaskData($value)
	{
		$value['end_time'] = strtotime($value['end_time']);
		$value['start_time'] = strtotime($value['start_time']);
		$res = $this->save([
		    'adminid'  => $value['adminid'],
		    'start_time' => $value['start_time'],
				'end_time' => $value['end_time'],
				'status' => $value['status'],
				'title' => $value['title'],
			]);
		return $res;
	}
}
