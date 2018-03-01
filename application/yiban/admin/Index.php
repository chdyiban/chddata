<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

/**
 * yiban管理后台
 * @package app\sign\admin
 */
class Index extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }
    public function test(){

    }

    public function index(){

    }

    public function showFace(){
        $page = input('get.page');
        dump($page);
        $this->assign('page',$page);
        $this->assign('prev_page',$page-1);
        $this->assign('next_page',$page+1);
        $path = 'public'.DS.'face'.DS.'zp'.DS;
        $face = $this->getFile($path);

        $this->assign('face',$face);

        return parent::fetch(); // 渲染模板
    }

    private function getFile($path){
        $tree = array(); 
        foreach(glob($path.DS.'*') as $key => $single){ 
            if(is_dir($single)){ 
                $tree = array_merge($tree,$this->getFile($single)); 
            } 
            else{ 
                
                $tree[$key]['src'] = $single;
                $subStr1 = explode('/', $single);
                $subStr2 = explode('.',end($subStr1));
                $tree[$key]['title'] = $subStr2[0];
            } 
        } 
        return $tree; 

    }

}