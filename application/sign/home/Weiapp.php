<?php
namespace app\sign\home;

use app\index\controller\Home;
use think\Request;
use think\Db;
use think\Cookie;
use zoujingli\wechat;
/**
 * 微信控制接口
 * @package app\sign\home
 */
class Weiapp extends Home
{
	public function openid(){
		$appid = 'wx1078b0b4e379a188';
		$secret = 'f7f32246397157d20190b6557d9fefdc';
		$code = Request::instance()->get('code');

		$url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';

		$retData = array();
		if(isset($code)){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$data = curl_exec($curl);
			curl_close($curl);
		}
		$data = json_decode($data);
		$retData['openid'] = $data->openid;
		$retData['errMsg'] = 'ok';

    	return json($retData);
	}


}