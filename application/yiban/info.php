<?php
/**
 * 模块信息
 */
return [
    // 模块名[必填]
    'name'        => 'yiban',
    // 模块标题[必填]
    'title'       => '易班',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'yiban.yang.module',
    // 开发者[必填]
    'author'      => 'Yang',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    'version'     => '1.0.0',
    'icon'        => 'fa fa-fw fa-yoast',
    'need_module' => [
        ['admin', 'admin.dolphinphp.module', '1.0.0']
    ],
    // 'tables' => [
    //     'iask_content',
    // ],
    'database_prefix' => 'dp_',
    // 模块描述 
    'description' => '易班轻应用后台',
    // // // 参数配置
    // 'config' => [
    //     ['text', 'appid', 'AppId', '应用ID，登录 yiban 查看'],
    //     ['text', 'secret', 'AppSecret', '应用密钥，登录 yiban 查看'],
    //     ['text', 'callback', 'CallBack', '令牌，用于接口验证，登录 yiban查看'],
    // ]

];