<?php
/**
 * 模块信息
 */
return [
    // 模块名[必填]
    'name'        => 'sign',
    // 模块标题[必填]
    'title'       => '晚点名',
    // 模块唯一标识[必填]，格式：模块名.开发者标识.module
    'identifier'  => 'sign.yang.module',
    // 开发者[必填]
    'author'      => 'Yang',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    'version'     => '1.0.0',
    'need_module' => [
        ['admin', 'admin.dolphinphp.module', '1.0.0']
    ],
    'tables' => [
        'wx_sign',
        'wx_user'
    ],
    'database_prefix' => 'wx',
    // 参数配置
    'config' => [
        ['radio', 'need_check', '是否需要审核', '4', ['1' => '是', '0' => '否'], 1],
        ['radio', 'comment_status', '是否开启评论', '是否开启文章评论功能', ['1' => '是', '0' => '否'], 1]
    ]

];