<?php
namespace app\face\home;

use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;

/**
 * 人脸识别前端控制器
 * @package app\yiban\home
 */
class Index extends Home
{
    private $api_key = 'v0vl4xA5TCEpMW6e5eJjh8iECpdBzLDx';
    private $api_secret = 'keP3GSB5BGfH5VMMznblp81AtH-eyQ3I';

	const DETECT_API = 'https://api-cn.faceplusplus.com/facepp/v3/detect';
	const FACESET_CREATE_API = 'https://api-cn.faceplusplus.com/facepp/v3/faceset/create';
	const FACESET_ADDFACE_API = 'https://api-cn.faceplusplus.com/facepp/v3/faceset/addface';
	const SEARCH_API = 'https://api-cn.faceplusplus.com/facepp/v3/search';

	public function index(){
        $outer_id = 'chd_test_outer_id';

		//4d23b437c16425ab03e470423a17d10d
		//$image = ROOT_PATH.'public'.DS.'face'.DS."白佳男 计算机类 计算机一班 陕西省榆林市.jpg";

        //5879a7793ec59f38c7ab48b118b60821
		$image = ROOT_PATH.'public'.DS.'face'.DS."房晓宇 计算机类 计算机一班 河北省石家庄市.jpg";
		$face_token = $this->getFaceToken($image);
		echo 'face_token:'.$face_token.'<br/>';
    	
    	//$result = $this->faceset_addface($face_token,$outer_id);

        $result = $this->search($face_token,$outer_id);
        dump(json_decode($result,true));

    	
    	//dump($result);
    	
	}

    protected function detectByPicUrl($pic){
        $curl = curl_init();   
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::DETECT_API,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'api_key'=>$this->api_key,
                'api_secret'=>$this->api_secret,

                'image_url'=>$pic,

                //'image_file";filename="image'=>"$content",
                
                'return_landmark'=>"0",
                'return_attributes'=>"gender,age,beauty,skinstatus,emotion"
            ), 
            CURLOPT_HTTPHEADER => array("cache-control: no-cache",),
        ));   
        $response = curl_exec($curl);
        $err = curl_error($curl);   
        curl_close($curl);   
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function test(){
        //faceset_token = caedc60e807f53d79920145d0136290b
        $image = ROOT_PATH.'public'.DS.'face'.DS."test1.jpg";
        $result = $this->detect($image);
        dump(json_decode($result,true));

    }


	private function getFaceToken($file){
        $face_token = '';
		$result = json_decode($this->detect($file),true);


    	if(array_key_exists('error_message',$result)){
    		//失败处理
    	}else{
    		$face_token = $result['faces'][0]['face_token'];
    	}	
    	//$result = $this->faceset_create();
    	return $face_token;

	}

	private function detect($image){
		$fp = fopen($image, 'rb');
    	$content = fread($fp, filesize($image)); 

		$curl = curl_init();   
    	curl_setopt_array($curl, array(
    		CURLOPT_URL => self::DETECT_API,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => "",
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 30,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => "POST",
    		CURLOPT_POSTFIELDS => array(
    			'api_key'=>$this->api_key,
    			'api_secret'=>$this->api_secret,

    			'image_file";filename="image'=>"$content",
    			
    			'return_landmark'=>"0",
    			'return_attributes'=>"gender,age,beauty,skinstatus,emotion"
    		), 
    		CURLOPT_HTTPHEADER => array("cache-control: no-cache",),
    	));   
    	$response = curl_exec($curl);
    	$err = curl_error($curl);   
    	curl_close($curl);   
    	if ($err) {
    	    return "cURL Error #:" . $err;
    	} else {
    	    return $response;
    	}
	}

	/**
	* TEST:
	* faceset_token : 2907fc19e344e253ddb67fe81e23bef4
	* outer_id : the_only_flag_of_outer_id
	*/
	private function faceset_create(){
		$curl = curl_init();   
    	curl_setopt_array($curl, array(
    		CURLOPT_URL => self::FACESET_CREATE_API,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => "",
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 30,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => "POST",
    		CURLOPT_POSTFIELDS => array(
    			'api_key'=>$this->api_key,
    			'api_secret'=>$this->api_secret,

    			'display_name'=>'chd_test_display_name',
    			'outer_id'=>'chd_test_outer_id',    			
    		), 
    		CURLOPT_HTTPHEADER => array("cache-control: no-cache",),
    	));   
    	$response = curl_exec($curl);
    	$err = curl_error($curl);   
    	curl_close($curl);   
    	if ($err) {
    	    return "cURL Error #:" . $err;
    	} else {
    	    return $response;
    	}
	}

	private function faceset_addface($face_tokens,$outer_id){
		$curl = curl_init();   
    	curl_setopt_array($curl, array(
    		CURLOPT_URL => self::FACESET_ADDFACE_API,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => "",
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 30,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => "POST",
    		CURLOPT_POSTFIELDS => array(
    			'api_key'=>$this->api_key,
    			'api_secret'=>$this->api_secret,

    			//'faceset_token'=>'eb86cd13b82c3efd6c72c9729bf88c94',
    			'outer_id'=>$outer_id,
    			'face_tokens'=>$face_tokens,    			
    		), 
    		CURLOPT_HTTPHEADER => array("cache-control: no-cache",),
    	));   
    	$response = curl_exec($curl);
    	$err = curl_error($curl);   
    	curl_close($curl);   
    	if ($err) {
    	    return "cURL Error #:" . $err;
    	} else {
    	    return $response;
    	}
	}

	private function search($face_token,$outer_id){
		$curl = curl_init();   
    	curl_setopt_array($curl, array(
    		CURLOPT_URL => self::SEARCH_API,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => "",
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 30,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => "POST",
    		CURLOPT_POSTFIELDS => array(
    			'api_key'=>$this->api_key,
    			'api_secret'=>$this->api_secret,

    			//'faceset_token'=>'eb86cd13b82c3efd6c72c9729bf88c94',
    			'face_token'=>$face_token,
    			'outer_id'=>$outer_id,
    			'return_result_count'=>2,    			
    		), 
    		CURLOPT_HTTPHEADER => array("cache-control: no-cache",),
    	));   
    	$response = curl_exec($curl);
    	$err = curl_error($curl);   
    	curl_close($curl);   
    	if ($err) {
    	    return "cURL Error #:" . $err;
    	} else {
    	    return $response;
    	}
	}
}