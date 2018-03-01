<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 本科教务
 * @package app\yiban\bkjw
 */
class Sports extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    public function index(){
    	$order = $this->getOrder();
        // 获取查询条件
        $map = $this->getMap();

        $btn_export = [
            'title' => '批量导出',
            'icon'  => 'fa fa-fw fa-key',
            'href'  => url('exportAll')
        ];


        // 数据列表
        $data_list = Db::view('SportsIndex','id,stu_id,type_id,event_id')
            ->view('SportsList2018','event_id,type_id,event_name,type_name','SportsIndex.event_id = SportsList2018.event_id')
            ->view('YibanBaseInfo','number,name,sex,class,mobile,college','SportsIndex.stu_id = YibanBaseInfo.number')
            ->paginate();

        // // 使用ZBuilder快速创建数据表格
        //高能，数据表设计是反的，即：event_id->type_name，type_id->event_name
        return ZBuilder::make('table')
            ->setPageTitle('春季运动会报名列表')
            // ->setTableName('admin_role') // 设置表名
            ->setSearch(['name' => '姓名', 'type_name' => '项目名称']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['college','学院'],
                ['event_name','项目类别'],
                ['type_name','项目名称'],
                ['stu_id', '学号'],
                ['name', '姓名'],
                ['sex','性别'],
                ['class','班号'],
                ['mobile','联系方式']
            ])
            ->addFilter('YibanBaseInfo.college')
            ->addTopButton('custom', $btn_export) // 添加授权按钮
            ->addTopButtons('add,delete') // 批量添加顶部按钮
            ->addRightButtons('edit,delete') // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
    }

    public function exportAll(){
        $title = 'export_sports_detail_'.date("Y_m_d__H_i_s");
        // 查询数据
        $data = Db::view('SportsIndex','id,stu_id,type_id,event_id')
            ->view('SportsList2018','event_id,type_id,event_name,type_name','SportsIndex.event_id = SportsList2018.event_id')
            ->view('YibanBaseInfo','number,name,sex,class,mobile,college','SportsIndex.stu_id = YibanBaseInfo.number')
            ->select();
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['college','auto','学院'],
            ['event_name','auto','项目类别'],
            ['type_name','auto','项目名称'],
            ['stu_id', 'auto','学号'],
            ['name','auto', '姓名'],
            ['sex','auto','性别'],
            ['class','auto','班号'],
            ['mobile','auto','联系方式']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [$title, $cellName, $data]);
    }
}