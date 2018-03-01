<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * 职业能力大赛：我认我生页面，由于其数据特殊性，放到后台管理面板。
 * @package app\yiban\wrws
 */
class Wrws extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    public function index(){
        $name = input('get.name');
        $this->assign('name',$name);
        $path = 'public'.DS.'face'.DS.$name;
        $face = $this->getFile($path);
        $face_num = count($face);
        $face = $this->vip($face,$name);
        if(count($face) <= 5 || $name == ''){//此时face的个数有可能改变 所以再统计一次
            $this->assign('all_face','{}');
            $this->assign('face','');
        }else{

            $this->assign('face_num',$face_num);
            //$roll_face 用作前端随机抽取时的切换显示
            if(count($face) >= 10){
                $roll_face = $this->randExtract($face,10);
                $this->assign('all_face',json_encode($roll_face));
            }else{
                $this->assign('all_face',json_encode($face));
            }
            
            $rand_face = $this->randExtract($face,5);
            $this->assign('face',$rand_face);
        }   
    	return parent::fetch(); // 渲染模板

    }

    /*
    * 遍历随机抽取照片
    * @params $face,$num
    * @return 
    */
    private function randExtract($face,$num){
        $rand_face = array();
        $rand_face_key = array_rand($face,$num);
        foreach ($rand_face_key as $key => $value) {
            # code...
            $rand_face[] = $face[$value];
        }
        return $rand_face;
    }

    private function vip($face,$name){
        $fake_face = array();
        switch ($name) {
            case '丁芝娟':
                $known_name = array('韩豆','范佳伟','戴汐娟','刁海轩','徐凡雨','于水欢','王炎','周晓琳','杜小伟','王一卓','刘璐');
                break;
            case '杨加玉':
                $known_name = array('马书畅','高成林','柯一滨','李绍骞','肖禹嵩','张旭阳','王一卓','刘璐','王巍','朱湘澄','尹晓博','刘涛','吕兆鹤','朱益辉','李思进','杨啸','葛耀阳','衡文毓','黄杰','黄浩','黄科凌');
                break;
            default:
                # code...
                break;
        }

        if(empty($known_name)){
            return $face;
        }else{
            foreach ($known_name as $k => $value) {
                # code...
                foreach ($face as $value) {
                    $pos = strpos($value['title'],$known_name[$k]);
                    if ($pos === false){
    
                    }else{
                        $fake_face[] = $value;
                    }
                }
            }
            return $fake_face;

        }
    }

    private function getFile($path){
        $tree = array(); 
        foreach(glob($path.DS.'*') as $key => $single){ 
            if(is_dir($single)){ 
                $tree = array_merge($tree,$this->getFile($single)); 
            } 
            else{ 
                
                $tree[$key]['src'] = $single;
                $subStr1 = explode(DS, $single);
                $subStr2 = explode('.',end($subStr1));
                $tree[$key]['title'] = $subStr2[0];
            } 
        } 
        return $tree; 

    }

}