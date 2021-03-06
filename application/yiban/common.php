<?php

// yiban公共函数库
function create_guid($namespace = '') {  
  static $guid = '';
  $uid = uniqid("", true);
  $data = $namespace;
  $data .= $_SERVER['REQUEST_TIME'];
  $data .= $_SERVER['HTTP_USER_AGENT'];
  $data .= $_SERVER['SERVER_ADDR'];
  $data .= $_SERVER['SERVER_PORT'];
  $data .= $_SERVER['REMOTE_ADDR'];
  $data .= $_SERVER['REMOTE_PORT'];
  $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
  $guid =  substr($hash, 0, 8) .
      '-' .
      substr($hash, 8, 4) .
      '-' .
      substr($hash, 12, 4) .
      '-' .
      substr($hash, 16, 4) .
      '-' .
      substr($hash, 20, 12);
  return $guid;
}

//模拟CURL提交
function sendRequest($uri,$post_data = ''){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Yi OAuth2 v0.1');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        if(isset($post_data) && $post_data != ''){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        
        $response = curl_exec($ch);
        return $response;
    }
//无论如何都是获取上次的任务信息
function getLastSignTask(){
    $time = time();
    $todayStartTime = mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
    $todayEndTime = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
    //$adminId = $this->getStuAdminId();
    $adminId = 1;
    //获取距离当前时间最近的上一次已经结束的晚点名任务
    $task = Db::table('dp_sign_task')
        ->where('end_time','<',$time)
        ->order('start_time Desc,id DESC')
        ->find();
    return $task;
  }