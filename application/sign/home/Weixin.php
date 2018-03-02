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
class Weixin extends Home
{
	private $wechat;

	private $config = array(
		'token'          => '',
    	'appid'          => 'wx42e32c9ed990e2f7',
    	'appsecret'      => '07989bb00172de25844a8b2ffbdfe025',
    	'encodingaeskey' => '',
	);

	public function _initialize(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST');

        Cookie::init(['prefix'=>'chd_','expire'=>7200,'path'=>'/']);

        $this->wechat = &\Wechat\Loader::get('Oauth', $this->config);
    }

    public function index(){
    	
    	//$callback = 'http://yjy.s1.natapp.cc/dolphinphp/index.php/sign/weixin/oauthAccessCode';
		$callback = 'http://yjy.s1.natapp.cc/dolphinphp/m/#/';
        $type = Request::instance()->get('type') == 1 ? 'snsapi_userinfo' : 'snsapi_base';
    	$retRedirect = $this->wechat->getOauthRedirect($callback,'STATE',$type);
    	$data['code'] = '0x200';
    	$data['message'] = 'ok';
    	$data['url'] = $retRedirect;
    	return json($data);
    }

    public function oauthAccessCode(){
    	$ret_code = $this->wechat->getOauthAccessToken();

    	if(empty($ret_code['access_token'])){
            $data['code'] = '0x900';
            $data['message'] = $ret_code['errmsg'];
    	}else{
            //获取用户信息
            $user_info = $this->wechat->getOauthUserinfo($ret_code['access_token'],$ret_code['openid']);

            $user_exist = Db::name('wx_user')->where('openid',$ret_code['openid'])->find();
            $db_data = [
                'access_token' => $ret_code['access_token'],
                'expires_in' => $ret_code['expires_in'],
                'refresh_token' => $ret_code['refresh_token'],
                'nickname' => $user_info['nickname'],
                'sex' => $user_info['sex'],
                'imgurl' => $user_info['headimgurl']
            ];
            if($user_exist){
                $user_query = Db::name('wx_user')->where('openid',$ret_code['openid'])->update($db_data);
            }else{
                $db_data['openid'] = $ret_code['openid'];
                $user_query = Db::name('wx_user')->insert($db_data);
            }
            if($user_query){
                // Cookie::set('token',$ret_code['openid']);
                //getOauthUserinfohere
                $data['code'] = '0x200';
                $data['message'] = 'ok';
                $data['openid'] = $ret_code['openid'];
            }else{
                $data['code'] = '0x800';
                $data['message'] = 'db error';
            }
        }
    	return json($data);
    }

    public function getOauthUserinfo(){
    	// 刷新access token 并续期
    	// $retToken = $this->wechat->getOauthRefreshToken($retCode['refresh_token']);
    	
        // $retAuthCheck = $this->wechat->getOauthAuth($ret_code['access_token'],$ret_code['openid']);
  		// dump($retAuthCheck);

    	$retUserInfo = $this->wechat->getOauthUserinfo($ret_code['access_token'],$ret_code['openid']);
    	// echo '123';
    	dump($retUserInfo);
        // 	["openid"] = "ogN_5t_v8T2pNgSSdhlcTJMqGbwY";
  		// ["nickname"] = "杨加玉";
  		// ["sex"] = 1;
  		// ["city"] = "Xi'an";
  		// ["province"] = "Shaanxi";
  		// ["country"] = "CN";
  		// ["headimgurl"] = "";

        return $ret_code;
    }
}