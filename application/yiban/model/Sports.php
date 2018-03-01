<?php

namespace app\yiban\model;

use think\Model;

class Sports extends Model
{
	protected $table = 'dp_sports_index';

	public function submit($insert_data){

		$stu_id = $insert_data[0]['stu_id'];

		$this->where('stu_id = '.$stu_id)->delete();

		if($this->saveAll($insert_data)){
			return true;
		}else{
			return false;
		}
		
	}

	public function getSportsListById($stu_id){

		$map['status'] = 1;
		$map['stu_id'] = $stu_id;
		return $this->where($map)->field('type_id,event_id')->select();

	}
}