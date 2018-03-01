<?php
namespace app\yiban\home;

use think\Log;
use think\Db;
use think\Session;
use yiban\YBOpenApi;
use app\yiban\model\InsureInfo as InsureModel;
use app\yiban\validate\InsureInfo;

/**
 * 2017新生医保填报控制器
 * @package app\yiban\home
 */
class Yibao extends Api
{
	public function isVerify(){
    	$code = input('get.verify_request');
    	$token = $this->getToken($code);

    	$initData = array();
    	//未获得授权
    	if($token == null){
    		$initData['status'] = 'oauth';
			$initData['info'] = '未获得易班授权，跳转中';
			return json($initData);
    	}
    	
    	//判断是否进行校级认证

    	//
    	$uri = $this->url_real_me.'?access_token='.$token;
		$result = sendRequest($uri);

		$result = json_decode($result);

		if(!$this->checkGrade2017($result->info->yb_studentid)){
    		$initData['status'] = 'old';
			$initData['info'] = '尚未进行校方认证！';
			return json($initData);
    	}
		
		if($result->status == 'success'){
			//存储逻辑之后，删除不需要暴露的信息
			$initData['status'] = 'success';
			$initData['info'] = $this->refreshYibanInfo($result->info);
			$initData['complete'] = $this->checkInsuredComplete($result->info->yb_userid);
		}else{
			$initData['status'] = 'error';
			$initData['info'] = 'error!';
		}
		return json($initData);
    }

    public function postForm(){
    	$token = input('get.token');
    	$tokenInfo = json_decode($this->decrypts($token));
    	if(!isset($tokenInfo->visit_user->userid)){
    		return false;
    	}
    	$yb_userid = $tokenInfo->visit_user->userid;

    	$postData = input('post.');
    	if($postData['yb_userid'] == '' || $yb_userid != $postData['yb_userid']){
    		$retData['status'] = 'error';
    		$retData['info']['msg'] = '数据校验不合法';
    		return json($retData);
    	}

    	//验证逻辑及存储逻辑
    	$infoModel = new InsureModel;

    	$data['id_card_num'] = $postData['idCardNum'];
    	$data['insured_sex_code'] = $postData['sex'];
    	$data['birthday'] = $postData['birthday'];
    	$data['insured_nation_code'] = $postData['nation'];
    	$data['insured_date'] = $postData['CBRQ'];
    	$data['length_of_schooling'] = $postData['XZ'];
    	$data['class_name'] = $postData['BJMC'];
    	$data['mobile'] = $postData['mobile'];
    	$data['special_code'] = $postData['special'];
    	$data['domicile'] = $postData['HJSZD'];
    	$data['home_address'] = $postData['JTZC'];
    	$data['contact_person'] = $postData['contactPerson'];
    	$data['contact_person_mobile'] = $postData['contactPersonMobile'];
    	$data['insured_update_time'] = time();

    	$valCheck = $this->validate($data,'InsureInfo');
    	if(true !== $valCheck){
    		$retData['status'] = 'error';
    		$retData['info']['msg'] = $valCheck;
    		return json($retData);
    	}

    	$result = $infoModel->where('yb_userid',$yb_userid)->update($data);

    	if($result){
    		$retData['status'] = 'success';
    		$retData['info']['msg'] = '提交成功';
    	}else{
    		$retData['status'] = 'error';
    		$retData['info']['msg'] = $infoModel->getError();
    	}

    	return json($retData);
    }

    public function getAllForm(){
    	$token = input('get.token');
    	$tokenInfo = json_decode($this->decrypts($token));
    	if(!isset($tokenInfo->visit_user->userid)){
    		$retData['status'] = 'error';
    		$retData['info']['msg'] = 'token不合法';
    		return json($retData);
    	}

    	$yb_userid = $tokenInfo->visit_user->userid;

    	$infoModel = new InsureModel;
    	$result = $infoModel->where('yb_userid',$yb_userid)->find();
    	$retData = array();
    	$retData['status'] = 'success';

    	$retData['info'] = [

    		['label' => '证件类型','value' => '1'],
    		['label' => '身份证号码','value' => $result['id_card_num']],
    		['label' => '性别代码','value' => $result['insured_sex_code']],
    		['label' => '民族代码','value' => $result['insured_nation_code']],
    		['label' => '出生日期','value' => $result['birthday']],
    		['label' => '参保日期','value' => $result['insured_date']],
    		['label' => '学籍编号','value' => $result['number']],
    		['label' => '入学年月','value' => '201709'],
    		['label' => '学制代码','value' => $result['length_of_schooling'] ],
    		['label' => '班级名称','value' => $result['class_name']],
    		['label' => '班级编号','value' => $result['class']],
    		['label' => '所学专业','value' => $result['major']],
    		['label' => '人员类型代码','value' => '21'],
    		['label' => '个人电话','value' => $result['mobile']],
    		['label' => '特殊情况代码','value' => $result['special_code']],
    		['label' => '现居住地址','value' => '长安大学渭水校区'],
    		['label' => '户籍所在地','value' => $result['domicile']],
    		['label' => '家庭住址','value' => $result['home_address']],
    		['label' => '家庭联系人','value' => $result['contact_person']],
    		['label' => '联系人电话','value' => $result['contact_person_mobile']]
    	];
    	$retData['extra']['realname'] = $result['name'];
    	return json($retData);
    }

    private function refreshYibanInfo($data){
    	if(isset($data->yb_studentid)){
    		$update = Db::name('yiban_base_info')->where('number',$data->yb_studentid)->update([
    			'yb_userid' => $data->yb_userid,
    			'yb_userhead' => $data->yb_userhead,
    		]);
    		$get = Db::name('yiban_base_info')->where('number',$data->yb_studentid)->field('yb_userid,name,number,id_card_num,insured_sex_code,insured_nation_code,birthday,length_of_schooling,class_name,mobile,special_code,domicile,home_address,contact_person,contact_person_mobile')->find();
    	}
    	return $get;
    }

    //检查医保信息是否填写完成
    private function checkInsuredComplete($yb_userid){
    	$infoModel = new InsureModel;
    	$result = $infoModel->where('yb_userid',$yb_userid)->find();
		$resultArray = $result->toArray();
		unset($resultArray['stage']);
		unset($resultArray['head_img']);
		unset($resultArray['college']);
		$flag = true;
		foreach ($resultArray as $key => $value) {
			if($result[$key] === ''){
				$flag = false;
				break;
			}
		}
		return $flag;
    }
}