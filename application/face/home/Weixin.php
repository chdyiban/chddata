<?php
namespace app\face\home;

//use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;

use WeChat\Receive;
use WeChat\Menu;

/**
 * 人脸识别微信控制器
 * @package app\yiban\home
 */
class Weixin extends Index
{
	private $config = [
	    'token'          => 'UyxzFvyhthYfsTXt3gGT6f3tgx3f6jYG',
	    'appid'          => 'wx42e32c9ed990e2f7',
	    'appsecret'      => '07989bb00172de25844a8b2ffbdfe025',
	    //'encodingaeskey' => '',
	    // 配置商户支付参数（可选，在使用支付功能时需要）
	    //'mch_id'         => "1235704602",
	    //'mch_key'        => 'IKI4kpHjU94ji3oqre5zYaQMwLHuZPmj',
	    // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
	    //'ssl_key'        => '',
	    //'ssl_cer'        => '',
	    // 缓存目录配置（可选，需拥有读写权限）
	    //'cache_path'     => '',
	];

	public function index(){

		try{
			$api = new Receive($this->config);
			$msgType = $api->getMsgType();
			$openid = $api->getOpenid();
			$data = '';

			switch ($msgType) {
				case 'image':
					$pic = $api->getReceive('PicUrl');
					$user = $api->getReceive('FromUserName');

					$result = json_decode($this->detectByPicUrl($pic),true);

					$response = '';

					if(array_key_exists('error_message',$result)){
						$response = $result['error_message'];
					}elseif(empty($result['faces'])){
						$response = '未检测到人脸';
					}else{
						$face_num = count($result['faces']);
						$response = "检测到 $face_num 张人脸 \r\n";
						if($result['faces'][0]['attributes']['gender']['value'] == 'Male'){
							$response .= "性别:男 \r\n";
							$response .= '颜值:'.$result['faces'][0]['attributes']['beauty']['male_score']."\r\n";
						}else{
							$response .= "性别:女 \r\n";
							$response .= '颜值:'.$result['faces'][0]['attributes']['beauty']['female_score']."\r\n";
						}

						$response .= '年龄:'.$result['faces'][0]['attributes']['age']['value']."\r\n";

						$response .= "系统检索可能为：";

						


					}


					$api->text($response)->reply();
					# code...
					break;
				
				default:
					//$eventKey = $api->getReceive('EventKey');
					$api->text('123')->reply();
					# code...
					break;
			}

		}catch (Exception $e) {
		    // 处理异常
		    echo $e->getMessage();
		}


	}

	public function init(){

		
	
	}

	public function getmenu(){

		$api = new Menu($this->config);
		$menu = $api->get();
		dump($menu);
	}

	public function setMenu(){

		$newmenu =  array(
			"button"=>
				array(
					array(
						'type'=>'pic_photo_or_album',
						'name'=>'人脸检索',
						'key'=>'face_search'
					),
					array(
						'type'=>'click',
						'name'=>'数据浏览',
						'key'=>'data_scan'
					),
				)
		);
		$api = new Menu($this->config);
		$result = $api->create($newmenu);
		dump($result);
	}

}