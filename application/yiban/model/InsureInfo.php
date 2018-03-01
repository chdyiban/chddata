<?php

namespace app\yiban\model;

use think\Model;

class InsureInfo extends Model
{
	protected $table = 'dp_yiban_base_info';
	protected $_map = [
		['sex' => 'insured_sex'],
		['idCardNum' => 'id_card_num'],
		['nation' => 'nation_code'],
		['CBRQ' => 'insured_date'],
		['XZ' => 'length_of_schooling'],
		['BJMC' => 'class_name'],
		['special' => 'special_code'],
		['HJSZD' => 'domicile'],
		['JTZZ' => 'home_address'],
		['contactPerson' => 'contact_person'],
		['contactPersonMobile' => 'contac_person_mobile']
	];

	public function getYibanInfo($yb_studentid){
		return $this->where('number',$yb_studentid)->field(
				'yb_userid,name as username,number,class,id_card_num,insured_sex_code,insured_nation_code,birthday,length_of_schooling,class_name,mobile,special_code,domicile,home_address,contact_person,contact_person_mobile'
			)->find();
	}

}