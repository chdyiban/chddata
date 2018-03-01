<?php

namespace app\yiban\validate;

use think\Validate;

class InsureInfo extends Validate
{
    protected $rule = [
        'id_card_num' => [
            'regex'=>'/^(^\d{18}$|^\d{17}(\d|X|x))$/',
        ],
        'insured_sex_code' => 'require|between:1,2',
        'nation_code' => 'between:1,99',
        'birthday' => 'require|date',
        'insured_date' => 'require|date',
        'length_of_schooling' => 'require|between:4,5',
        'class_name' => 'require|min:2',
        'mobile' => '/^1[34578]\d{9}$/',
        'special_code' => 'require|between:0,2',
        'domicile' => 'require|min:3',
        'home_address' => 'require|min:5',
        'contact_person' => 'require|chs',
        'contact_person_mobile' => [
            // 'require' => 'true',
            'regex' => '/^1[34578]\d{9}$/',
        ],
    ];
    
    protected $message = [
        'yb_userid.max'  =>  '用户ID错误',
        'id_card_num' =>  '身份证号格式错误',
        'insured_sex_code' => '性别代码不合法',
        'nation_code' => '民族代码不合法',
        'birthday' => '出生日期不合法',
        'insured_date' => '参保日期不合法',
        'length_of_schooling' => '学制代码选择错误',
        'class_name' => '班级名称太短',
        'mobile' => '手机号码不合法',
        'special_code' => '特殊情况代码不合法',
        'domicile' => '户籍所在地输入错误',
        'home_address' => '家庭住址输入错误',
        'contact_person' => '联系人名称必须为汉字',
        'contact_person_mobile' => '联系人手机号码不合法'
    ];
    
    // 自定义验证规则
    protected function checkName($value,$rule,$data)
    {
        return $rule == $value ? true : '名称错误';
    }
}