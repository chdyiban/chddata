<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\sign\home;

use app\index\controller\Home;
use think\Request;
use think\Db;
use think\Cookie;
/**
 * 点名上传控制器
 * @package app\sign\home
 */
class Api extends Home
{
    /**
    * API控制器初始化
    */
    public function _initialize(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST');
    }

    /**
    * 晚点名上传方法
    */
    public function index(){

    	//获取请求参数
    	$openid = Request::instance()->get('openid');
    	$timestamp = Request::instance()->get('noncestr');

    	$latitude = $this->checkLocationValid(Request::instance()->get('latitude','0'));
    	$longitude = $this->checkLocationValid(Request::instance()->get('longitude','0'));


    	//api请求参数检查，后期加密后变动
    	if(!$openid||!$timestamp||!$latitude||!$latitude){
    		//请求数据不合法
    		$data['code'] = '0x100';
    		$data['message'] = '定位尚未完成，请稍后';
    		return json($data);
    	}
    	
    	$userid = Db::table('dp_wx_user')->where('openid',$openid)->value('id');
    	if(!$userid){
    		//非法 返回
    		$data['code'] = '0x401';
    		$data['message'] = 'invalid access token';
    		return json($data);
    	}

    	$signed_check = Db::table('dp_sign_index')
    		->where('userid',$userid)
    		->where('signid',$this->getSignId())
    		->value('id');
    	if($signed_check){
    		$data['code'] = '0x402';
    		$data['message'] = '请不要重复签到';
    		return json($data);
    	}

    	$write = [
    		'signid' => $this->getSignId(),
    		'userid' => $userid,
    		'latitude' => $latitude,
    		'longitude' => $longitude
    	];
    	
    	if(Db::table('dp_sign_index')->insertGetId($write)){
    		$data['code'] = '0x200';
    		$data['message'] = 'ok';
    	}else{
    		$data['code'] = '0x500';
    		$data['message'] = 'insert error';
    	}
    	return json($data);
    }

    public function init(){

        $openid = Request::instance()->get('openid');
        $userid = Db::table('dp_wx_user')->where('openid',$openid)->value('id');
        if(!$userid){
            //非法 返回
            $data['code'] = '0x401';
            $data['message'] = 'invalid openid';
            return json($data);
        }

        $signid = $this->getSignId();
        $condition = [
            'userid' => $userid,
            'signid' => $signid
        ];

        //当前通知获取
        $notice_info = $this->getCurrentNotice();

        //签名状态获取
        $sign_info = $this->getSignInfo($signid);
        $sign_deadline = date('Y-m-d H:i',$sign_info['start_time']).'-'.date('H:i',$sign_info['end_time']);
        //!还应该增加超时判断
        if(Db::table('dp_sign_index')->where($condition)->find()){
            //可以查找到数据，说明已经签到
            $sign_status = '已签';
        }else{
            //未签到
            $sign_status = '未签';
        }


        $data = [
                'code' => '0x200',
                'message' => 'ok',
                'info' => [
                    'notice'=>$notice_info,
                    'personal'=>[
                        'task' => $sign_info['title'],
                        'deadline' => $sign_deadline,
                        'status' => $sign_status
                    ]
                ]
        ];
        return json($data);
    }

    private function checkLocationValid($location){
    	//if 不合法 return false
    	return $location;
    }

    private function getSignId(){
    	$sign_id = 4;
    	return $sign_id;
    }

    private function getSignInfo($signid){
        return Db::table('dp_sign_info')->where('id',$signid)->find();
    }

    private function getCurrentNotice(){
        $notice = Db::table('dp_sign_notice')->where('status',1)->field('id,title,notice as `desc`,timestamp')->order('sort DESC')->select();
        // 按道理讲，自己学院的学生只能收到自己学院的通知
        // 万一指定人群收到通知，如何如理？暂且默认全是学生工作部发的通知。
        return $notice;
    }

    /**
    * 点名合法性校验，即同一个userid在同一个点名任务signid下只能有出现一次，
    * 即每人只能在一个任务中签到一次
    */
    private function checkSignValid($openid){
    	//1.openid 合法性校验
    	$sign_id = $this->getSignId();
    }
}