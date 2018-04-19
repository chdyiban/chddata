<?php
namespace app\yiban\admin;

use think\Request;
use think\Cache;
use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 运动会计分
 * @package app\yiban\bkjw
 */
class Sportsscore extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    public function index(){

        $fields_add = [
            ['number', 'event_id', '项目id'],
            ['number', 'type_id', '项目类型id'],
            ['number', 'YXDM', '学院id'],
            ['number', 'ranking', '排名'],
            ['number', 'score', '得分'],
            ['text', 'remark', '备注'],
        ];

        $fields_edit = [
            ['hidden', 'id'],
            ['number', 'type_id', '项目类型id'],
            ['number', 'event_id', '项目id'],
            ['number', 'YXDM', '学院id'],
            ['number', 'ranking', '排名'],
            ['number', 'score', '得分'],
            ['text', 'remark', '备注'],
        ];

        // 授权按钮
        $btn_new_add = [
            'title' => '多组新增',
            'icon'  => 'fa fa-fw fa-key',
            'href'  => url('new_add')
        ];

        $order = $this->getOrder();
    	$map = $this->getMap();

        $data_list = Db::connect('chd_config')
            ->view('dp_sports_score')  
            ->view('dp_sports_list2018','event_id,type_id,event_name, type_name','dp_sports_list2018.event_id = dp_sports_score.event_id AND dp_sports_list2018.type_id = dp_sports_score.type_id ')
            ->view('chd_dict_college','YXDM, YXJC', 'dp_sports_score.YXDM = chd_dict_college.YXDM')  
            ->where($map)
            ->order($order)
            ->paginate();
    	
        return ZBuilder::make('table')
            ->setPageTitle('春季运动会成绩表')
            ->addColumns([ // 批量添加列
                ['event_name', '项目类型'],
                ['type_name','项目名称'],               
                ['YXJC', '学院'],
                ['ranking', '排名'],
                ['score', '得分'],
                ['remark', '备注']
            ])
            ->addColumn('right_button', '操作', 'btn')
            ->autoAdd($fields_add, 'sports_score', '', '', '', true)
            ->autoEdit($fields_edit, 'sports_score', '', '', '', true)
            ->addRightButton('delete', ['table' => 'sports_score'])
            ->addTopButton('custom', $btn_new_add) // 添加新增按钮
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
    }
    public function new_add(){
        // 保存数据
        if ($this->request->isPost()){
            // 表单数据
            $data = $this->request->post();
            $db = Db::name('sports_score');
            // 插入数据 
            $num = (count($data)-2)/4;
            for($i = 1; $i <= $num; $i++){
                if($data['id'.$i] != 0 && $data['id'.$i] != ''){
                    $res = $db->insert([
                        'event_id' => $data['event_id'],
                        'type_id' => $data['type_id'],
                        'YXDM' => $data['id'.$i],
                        'score' => $data['score'.$i],
                        'ranking' => $data['rank'.$i],
                        'event_id' => $data['event_id'],
                        'remark' => $data['remark'.$i],
                    ]);
                }
            } 
            if ($res == 1) {
                $this->success('新增成功', 'yiban/sportsscore/index');
            }
        }else{
            return ZBuilder::make('form')
                    ->setPageTitle('快速添加')
                    ->addNumber('event_id', '项目id')
                    ->addNumber('type_id', '项目类型id')
                    ->addNumber('id1', '学院id')
                    ->addNumber('rank1','名次')
                    ->addNumber('score1', '得分')
                    ->addtext('remark1','备注')
                    ->addNumber('id2', '学院id')
                    ->addNumber('rank2','名次')
                    ->addNumber('score2', '得分')
                    ->addtext('remark2','备注')
                    ->addNumber('id3', '学院id')
                    ->addNumber('rank3','名次')
                    ->addNumber('score3', '得分')
                    ->addtext('remark3','备注')
                    ->addNumber('id4', '学院id')
                    ->addNumber('rank4','名次')
                    ->addNumber('score4', '得分')
                    ->addtext('remark4','备注')
                    ->addNumber('id5', '学院id')
                    ->addNumber('rank5','名次')
                    ->addNumber('score5', '得分')
                    ->addtext('remark5','备注')
                    ->addNumber('id6', '学院id')
                    ->addNumber('rank6','名次')
                    ->addNumber('score6', '得分')
                    ->addtext('remark6','备注')
                    ->addNumber('id7', '学院id')
                    ->addNumber('rank7','名次')
                    ->addNumber('score7', '得分')
                    ->addtext('remark7','备注')
                    ->addNumber('id8', '学院id')
                    ->addNumber('rank8','名次')
                    ->addNumber('score8', '得分')
                    ->addtext('remark8','备注')
                    ->fetch();
            }
    }
    public function sportsDate(){

        $fields_add = [
            ['text', 'sports_name', '项目名称'],
            ['text', 'sports_group', '组别（男子组，女子组，混合组）'],
            ['text', 'sports_time', '比赛时间'],
            ['number', 'sports_day', '比赛日期'],
            ['number', 'status', '比赛状态'],
        ];

        $fields_edit = [
            ['hidden', 'id'],
            ['text', 'sports_name', '项目名称'],
            ['text', 'sports_group', '组别（男子组，女子组，混合组）'],
            ['text', 'sports_time', '比赛时间'],
            ['number', 'sports_day', '比赛日期'],
            ['number', 'status', '比赛状态'],
        ];
        
        $order = $this->getOrder();
        $map = $this->getMap();

        $data_list = Db::name('sports_date') 
            ->where($map)
            ->order($order)
            ->paginate();
    	
        return ZBuilder::make('table')
            ->setPageTitle('春季运动会成绩表')
            ->addColumns([ // 批量添加列
                ['sports_name', '项目名称'],
                ['sports_group','组别（男子组，女子组，混合组）'],               
                ['sports_time', '比赛时间'],
                ['sports_day', '比赛日期'],
                ['status', '比赛状态'],
            ])
            ->addColumn('right_button', '操作', 'btn')
            ->autoAdd($fields_add, 'sports_date', '', '', '', true)
            ->autoEdit($fields_edit, 'sports_date', '', '', '', true)
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
    }


}