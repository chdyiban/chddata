<?php
namespace app\yiban\home;

use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;
use util\CurlAutoLogin;
use util\simple_html_dom;
use think\Request;

use app\yiban\model\Sportsscore as SportsscoreModel;
use app\yiban\model\Sports as SportsModel;
/**
 * ID:6001 运动会：长安大学运动会计分榜
 * 
 * @package app\yiban\home
 */
class Sportsscore extends Home
{

    public function test(){
      $this->assgin();
      //return view();
    }

    public function index(){
        //用来获取赛程
        $sports_date = $this->get_sports_date();
        //用来获取学院分数
        $data = Db::table('chd_dict_college')
                ->where('YXDM','<>','1700')
                ->where('YXDM','<>','1800')
                ->where('YXDM','<>','1801')
                ->where('YXDM','<>','9999')
                ->where('YXDM','<>','5100')
                ->where('YXDM','<>','1500')
                ->where('YXDM','<>','1400')
                ->select();
        //用来获取学院的投票数
        $vote1 = array();
        $vote = array();
        foreach($data as $value){
          $vote1['YXDM'] = $value['YXDM'];
          $vote1['YXJC'] = $value['YXJC'];
          $vote1['vote'] = $this->get_vote($value['YXDM']);
          $vote[] = $vote1;
        }
         //对数组进行按照分数来排序
        foreach($vote as $val){
          $key_arrays_vote[]=$val['vote'];
        }
        array_multisort($key_arrays_vote,SORT_DESC,SORT_NUMERIC,$vote);
     
        $info = array();
        $message = array();
        foreach($data as $value){
            $message['YXDM'] = $value['YXDM'];
            $message['YXJC'] = $value['YXJC'];
            $message['score'] = $this->get_score($value['YXDM']);
            $message['detail'] = $this->get_detail($value['YXDM']);
            $info[] = $message;
       }

      //对数组进行按照分数来排序
      foreach($info as $val){
        $key_arrays[]=$val['score'];
      }
      array_multisort($key_arrays,SORT_DESC,SORT_NUMERIC,$info);
      //返回每个学院的名称和分数，按照从大到小来排序
      //return json($info);
     return view('index',[
       'info' => $info,
       'date' => $sports_date,
       'vote' => $vote,
     ]);	
    }

    //用来获取每个学院的总分
    private function get_score($YXDM){    
        $model = new SportsscoreModel;
        $data = $model->where('YXDM', $YXDM)->select();
        $sum = 0;
        foreach($data as $value){
            $sum += $value['score'];
        }
        return $sum;
    }

    //用来获取每个学院的具体比赛情况，这里进行了分类
    private function get_detail($YXDM){
      $data = Db::connect('chd_config')
      ->view('dp_sports_score')  
      ->view('dp_sports_list2018','event_id,type_id,event_name, type_name','dp_sports_list2018.event_id = dp_sports_score.event_id AND dp_sports_list2018.type_id = dp_sports_score.type_id ')
      ->view('chd_dict_college','YXDM, YXJC', 'dp_sports_score.YXDM = chd_dict_college.YXDM')  
      ->where('YXDM', $YXDM)
      ->select();

      $info = array();
      $array_one = array();
      $array_two = array();
      $array_three = array();
      if(empty($data)){
        $info[0] = '';
        $info[1] = '';
        $info[2] = '';
      }else{
        foreach($data as $value){
          switch($value['type_id']){
            case 0:
              $array_one[] = $value;
              $info[0] = $array_one;
              break;
            case 1:
              $array_two[] = $value;
              $info[1] = $array_two;
              break;
            case 2:
              $array_three[] = $value;
              $info[2] = $array_three;
              break;
          }
        }
        if(empty($info[0])){
          $info[0] = '';
        }
        if(empty($info[1])){
          $info[1] = '';
        }
        if(empty($info[2])){
          $info[2] = '';
        }
      }
      return $info;
    }
    
    //用来获取赛程的信息和状态
    private function get_sports_date(){
      $date = array();
      $data = Db::table('dp_sports_date')->select();
      foreach($data as $value){
        switch($value['sports_day']){
          case 1:
          switch($value['status']){
            case 1:
              $date[0][0][] = $value;
            break;        
            case 2:
              $date[0][1][] = $value;
            break;
            case 3:
              $date[0][2][] = $value;
            break;
          }
          if(empty($date[0][0])){
            $date[0][0] = '';
          }
          if(empty($date[0][1])){
            $date[0][1] = '';
          }
          if(empty($date[0][2])){
            $date[0][2] = '';
          }
          break;
          case 2:
          switch($value['status']){
            case 1:
              $date[1][0][] = $value;
            break;        
            case 2:
              $date[1][1][] = $value;
            break;
            case 3:
              $date[1][2][] = $value;
            break;
          }
          if(empty($date[1][0])){
            $date[1][0] = '';
          }
          if(empty($date[1][1])){
            $date[1][1] = '';
          }
          if(empty($date[1][2])){
            $date[1][2] = '';
          }
          break;
          case 3:
          switch($value['status']){
            case 1:
              $date[2][0][] = $value;
            break;        
            case 2:
              $date[2][1][] = $value;
            break;
            case 3:
              $date[2][2][] = $value;
            break;
          }
          if(empty($date[2][0])){
            $date[2][0] = '';
          }
          if(empty($date[2][1])){
            $date[2][1] = '';
          }
          if(empty($date[2][2])){
            $date[2][2] = '';
          }
          break;
          case 4:
          switch($value['status']){
            case 1:
              $date[3][0][] = $value;
            break;        
            case 2:
              $date[3][1][] = $value;
            break;
            case 3:
              $date[3][2][] = $value;
            break;
          }
          if(empty($date[3][0])){
            $date[3][0] = '';
          }
          if(empty($date[3][1])){
            $date[3][1] = '';
          }
          if(empty($date[3][2])){
            $date[3][2] = '';
          }
          break;
          case 5:
          switch($value['status']){
            case 1:
              $date[4][0][] = $value;
            break;        
            case 2:
              $date[4][1][] = $value;
            break;
            case 3:
              $date[4][2][] = $value;
            break;
          }
          if(empty($date[4][0])){
            $date[4][0] = '';
          }
          if(empty($date[4][1])){
            $date[4][1] = '';
          }
          if(empty($date[4][2])){
            $date[4][2] = '';
          }
          break;
          case 6:
          switch($value['status']){
            case 1:
              $date[5][0][] = $value;
            break;        
            case 2:
              $date[5][1][] = $value;
            break;
            case 3:
              $date[5][2][] = $value;
            break;
          }
          if(empty($date[5][0])){
            $date[5][0] = '';
          }
          if(empty($date[5][1])){
            $date[5][1] = '';
          }
          if(empty($date[5][2])){
            $date[5][2] = '';
          }
          break;
        }    
      }

      return $date;

    }

    //用来获取学院的热度人气
    private function get_vote($YXDM){
      $data = Db::name('sports_vote')
              ->where('YXDM',$YXDM)
              ->count();
      return $data;
    }

    //投票的方法
    public function vote(){
        $time = time();
        $request = Request::instance();
        $ip = $request->ip();
        $ip_nums = $this->check_ip($ip);
        $YXDM = $request ->get('id');
        $res = Db::name('sports_vote');
        $isHave = $res->where('ip',$ip)
                ->order('time DESC')
                ->select();
        $last_time = strtotime("-1 minutes");

        if($isHave){
            $nums = $this->check_ip($ip);
            if($nums > 30){
                $data['code'] = 403;
                $data['msg'] = '请一分钟后再试';
                return json($data);
            }else{
                $result = $res->insert([
                    'time' => $time,
                    'ip' => $ip,
                    'YXDM' => $YXDM,
                    ]);
                }
        }else{
            $result = $res->insert([
                'time' => $time,
                'ip' => $ip,
                'YXDM' => $YXDM,
            ]);
        }
        if($result){
            $data['code'] = 0;
            $data['msg'] = '投票成功';
            $data['piao'] = $this->get_vote($YXDM);
            return json($data);
        }

    }

    private function check_ip($ip){
        $time = time();
        $last_time = strtotime("-1 minutes");
        $number = Db::name('sports_vote')
                    ->where('ip',$ip)
                    ->where('time','<=',$time)
                    ->where('time','>=',$last_time)
                    ->order('time DESC')
                    ->count();
        return $number;
    }
}



