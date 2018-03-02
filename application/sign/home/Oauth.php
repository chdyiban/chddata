<?php

namespace app\sign\home;

use app\index\controller\Home;
use think\Request;
use think\Cookie;

Class Oauth extends Home{
	public function index(){
		$stu_num = Request::instance()->post('num');
		$stu_pwd = Request::instance()->post('pwd');

		//
		$stu_num = '2014124082';
		$stu_pwd = '69431589';
		//
		// $oauthStr = $this->getOauthString();

		// $data = [
		// 	'username' => $stu_num,
		// 	'password' => $stu_pwd,
		// 	'btn' => '',

		// 	'lt' => $oauthStr['lt'],
		// 	'dllt' => 'userNamePasswordLogin',
		// 	'execution' => $oauthStr['execution'],
		// 	'_eventId' => '_eventId',
		// 	'rmShown' => '1'

		// ];
		// dump($data);
		$ret = $this->curl();
		dump($ret);
		//$matches_lt[1][0]

	}

	private function getOauthString(){
		$url = 'http://ids.chd.edu.cn/authserver/login';
		$data = file_get_contents($url);
		$pattern_lt = '/<input type="hidden" name="lt" value="(.*?)"\/>/is';
		$pattern_execution = '/<input type="hidden" name="execution" value="(.*?)"\/>/is';
		preg_match_all($pattern_lt, $data, $matches_lt);
		preg_match_all($pattern_execution, $data, $matches_excution);
		$ret['lt'] = $matches_lt[1][0];
		$ret['execution'] = $matches_excution[1][0];
		return $ret;
	}

	private function curl(){
		$url = 'http://ids.chd.edu.cn/authserver/login';
		$cookie_file = tempnam("tmp","cookie");
		dump($cookie_file);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
		$data = curl_exec($ch);

		curl_close($ch);

		$pattern_lt = '/<input type="hidden" name="lt" value="(.*?)"\/>/is';
		$pattern_execution = '/<input type="hidden" name="execution" value="(.*?)"\/>/is';
		preg_match_all($pattern_lt, $data, $matches_lt);
		preg_match_all($pattern_execution, $data, $matches_excution);

		$post_data = [
			'username' => '2014124082',
			'password' => '69431589',
			'btn' => '',

			'lt' => $matches_lt[1][0],
			'dllt' => 'userNamePasswordLogin',
			'execution' => $matches_excution[1][0],
			'_eventId' => 'submit',
			'rmShown' => '1'

		];
		$headers = array();
		$headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
		$headers[] = 'Origin: http://ids.chd.edu.cn';
		$headers[] = 'Referer: http://ids.chd.edu.cn/authserver/login';
		// $headers = [
		// 	'User-Agent'=> 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
		// 	'Origin'=>'http://ids.chd.edu.cn',
		// 	'Referer'=>'http://ids.chd.edu.cn/authserver/login'
		// ];
		dump($post_data);
		dump($headers);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
		curl_setopt($ch, CURLOPT_POST, 1);    // post 提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		$output = curl_exec($ch);

		$result = curl_getinfo($ch);
		dump($result);
		curl_close($ch);
		return $output;
	}
}
