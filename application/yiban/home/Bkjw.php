<?php
namespace app\yiban\home;

use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;
use yiban\YBOpenApi;
use util\CurlAutoLogin;
use util\simple_html_dom;
use app\yiban\model\Bkjw as BkjwModel;
use app\yiban\model\BaseInfo as BaseInfoModel;
/**
 * ID:6000 本科教务：查成绩、查课表
 * 测试易班账号 ID: PW:
 * @package app\yiban\home
 */
class Bkjw extends Api
{
	public function _initialize()
    {

    	parent::_initialize();
    	$this->token = $this->getToken($this->verifyRequest);
    }
	/**
	* 是否绑定长安大学信息门户
	*/
	public function isBindPortal(){
		$stu_id = $this->getStudentId($this->token);

		if(empty($stu_id)){
			$data['status'] = 'error';
			$data['info'] = '参数错误';
			$data['code'] = '0x60002';
			return json($data);
		}
		$model = new BkjwModel;
		$query = $model -> isBind($stu_id);
		if($query){
			$data['status'] = true;
			$data['info'] = '已绑定';
		}else{
			$data['status'] = false;
			$data['info'] = '尚未绑定，即将前往绑定';
			$data['stu_id'] = $stu_id;
		}

		return json($data);
	}

	public function q(){
		$stu_id = $this->getStudentId($this->token);

		if(!is_numeric($stu_id)){
			$data['status'] = false;
			$data['info'] = '参数错误';
			$data['code'] = '0x60004';
			return json($data);
		}
		$password = (input('post.password')!='') ? input('post.password') : $this->getPassword($stu_id);

		if(empty($password)){
			$data['status'] = false;
			$data['info'] = '参数错误';
			$data['code'] = '0x60003';
			return json($data);
		}

		$captcha['captcha'] = input('post.captchaResponse');
		if($captcha['captcha']){
			$captcha['lt'] = input('post.lt');
			$captcha['execution'] = input('post.execution');

			if($captcha['lt'] == null || $captcha['execution'] == null){
				$data['status'] = false;
				$data['info'] = '验证环节参数错误';
				$data['code'] = '0x60008';
				return json($data);
			}			
		}

		$result = $this->scoreQuery($stu_id,$password,$captcha);
		$result['data']['me'] = $this->studentInfoQuery();

		$time = date('y年m月d日 h时i分s秒',time());
		$randStr = $this->randString();
		$content = '#易班查成绩# 我用易班查成绩，我在'.$time.'为长大易班打call。 【'.$randStr.'】';
		$comment = $this->sendComment($content);

		return json($result);
	}

	/**
	* 根据学号查询用户信息
	*/
	private function studentInfoQuery(){

		$stuInfo = $this->getStudentInfo($this->token);

		$model = new BaseInfoModel;
		$result = $model->saveStuInfo($stuInfo);
		return $result;
	}

	private function randString(){
		$typeId = rand(1,17);
		$pageIndex = rand(1,200);

		$url = 'http://apis.haoservice.com/lifeservice/JingDianYulu?key=9a79841ece0b4330b10786902df5a8a2&typeId='.$typeId.'&pageIndex='.$pageIndex.'&pageSize=1&paybyvas=false';
		$result = json_decode(sendRequest($url),true);
		if($result['error_code'] == '0'){
			return $result['result']['List'][0]['Content'];
		}else{
			return '';
		}
	}

	private function scoreQuery($username,$password,$captcha = array()){
        header('Content-Type: text/html; charset=utf-8');

        if(empty($username)||empty($password)){
        	$result['status'] = 'error';
        	$result['info'] = '学号密码不能为空';
        	$result['code'] = '0x60001';
        	return $result;
        }

        //$cookie_file = dirname(__FILE__).'/cookie'.$username.'.txt';
        $cookie_file = RUNTIME_PATH .'/cookie/cookie_'.$username.'.txt';

                //使用上面保存的cookies再次访问
        $url = "http://ids.chd.edu.cn/authserver/login?service=http://bkjw.chd.edu.cn/eams/teach/grade/course/person!search.action?semesterId=76";
        $post_data = array();
        $lt = '';
        $es = '';

        if($captcha['captcha']){
        	$post_data = array (
        	    "username" => $username,
        	    "password" => $password,
        	    "captchaResponse" => $captcha['captcha'], 
        	    "btn" => "登录",
        	    "lt" => $captcha['lt'],
        	    "dllt" => "userNamePasswordLogin",
        	    "execution" => $captcha['execution'],
        	    "_eventId" => "submit",
        	    "rmShown" => "1",
        	);

        }else{

        	$ch = curl_init($url); //初始化
        	curl_setopt($ch, CURLOPT_HEADER, 1); //不返回header部分
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
        	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
        	$response = curl_exec($ch);
        	// dump($response);
        	curl_close($ch);
	
        	$lt = explode('name="lt" value="', $response);
        	$lt = explode('"/>', $lt[1]);
        	$lt = $lt[0];
	
        	$es = explode('name="execution" value="', $response);
        	$es = explode('"/>', $es[1]);
        	$es = $es[0];
	
        	$post_data = array (
        	    "username" => $username,
        	    "password" => $password,
        	    "captchaResponse" => $captcha, 
        	    "btn" => "登录",
        	    "lt" => $lt,
        	    "dllt" => "userNamePasswordLogin",
        	    "execution" => $es,
        	    "_eventId" => "submit",
        	    "rmShown" => "1",
        	);
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //设置 CURLINFO_HEADER_OUT 选项之后 curl_getinfo 函数返回的数组将包含 cURL 请求的 header 信息
        // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_REFERER, "Referer: http://portal.chd.edu.cn/logout.portal"); 
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
        $response = curl_exec($ch);

        curl_close($ch);

        preg_match_all('/<th.*?>(.*?)<\/th?>/i', $response, $matches_header);
        preg_match_all('/<td.*?>(.*?)<\/td?>/si', $response, $matches);

        if(strpos($matches[1][0],'<strong>') !== false){
        	//查询失败，首先匹配错误msg
        	preg_match_all('/<span.*?id=\"msg\".*?>(.*?)<\/span?>/si', $response, $errMsg);
        	// dump($errMsg);
        	if(strpos($errMsg[1][0],'验证码') !== false){
        		// //试试执行一次空查询
        		// $this->captchaReset($cookie_file);
        		//需要输入验证码，执行验证码流程
        		$url = 'http://ids.chd.edu.cn/authserver/captcha.html';
        		$captcha = RUNTIME_PATH .'/captcha/'.$username.'.jpg';
        		$captchaUrl = '/runtime/captcha/'.$username.'.jpg';
        		$this->getCaptcha($url,$captcha,$cookie_file);
        		$result['status'] = 'need captcha';
        		$result['info'] = $errMsg[1][0];
        		$result['hidden']['lt'] = $lt;
        		$result['hidden']['execution'] = $es;
        		$result['captcha'] = $captchaUrl;
        		$result['code'] = '0x60006';
        		return $result;
        	}elseif (strpos($errMsg[1][0],'密码') !== false) {
        		# code...
        		$result['status'] = 'error';
        		$result['info'] = $errMsg[1][0];
        		$result['code'] = '0x60007';
        		return $result;
        	}
        	
        }

        //通过循环判断抓出的表格有几列
        $countColums = count($matches_header[1]);

        //当前有几门课
        $num = count($matches[1])/$countColums;

        if($num == 0){
        	//未出成绩
        }
        //门数循环
        for($i=0;$i<$num;$i++){
            //字段循环
            for($j=0;$j<$countColums;$j++){
                $score[$i][$j]['key'] = $matches_header[1][$j];
                $score[$i][$j]['val'] = trim($matches[1][$i*$countColums+$j]);
            }

        }

        $this->bind($username,$password);
        $this->scoreStore($username,json_encode($score));


        if(isset($score)){
        	$result['status'] = 'success';
        	$result['info'] = '查询成功';
        	$result['data']['num'] = $num;
        	$result['data']['score'] = $score;
        }else{
        	$result['status'] = 'error';
        	$result['info'] = '查询失败,请稍后再试';
        	$result['code'] = '0x60005';
        }

        return $result;
    }



    /*
	*@通过curl方式获取指定的图片到本地
	*@ 完整的图片地址
	*@ 要存储的文件名
	*/
	private function getCaptcha($url = "", $filename = "",$cookie_file){

		//去除URL连接上面可能的引号
		//$url = preg_replace( '/(?:^['"]+|['"/]+$)/', '', $url );
		$ch = curl_init();
		$fp = fopen($filename,'wb');
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_FILE,$fp);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使用上面获取的cookies
		//curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
		curl_setopt($ch,CURLOPT_TIMEOUT,60);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		return true;
	}

    /**
    * 存储/更新成绩
    * @param $scoreStr 成绩字符串
    * @return bool 更新成功或失败
    */
    private function scoreStore($stu_id,$score_str){

    	$model = new BkjwModel;
    	return $model->store($stu_id,$score_str);
    }

    /**
	* 绑定长安大学信息门户
	*/
	private function bind($username,$password){

		$model = new BkjwModel;
		$result = $model -> bind($username,$password);
		return $result;
	}
	
	/**
	* 获取信息门户密码
	*/
	private function getPassword($username){
		$model = new BkjwModel;
		return $model->isBind($username);
	}
}