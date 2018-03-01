<?php
namespace app\yiban\home;

use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;
use yiban\YBOpenApi;
use util\CurlAutoLogin;
use util\simple_html_dom;

use app\yiban\model\BaseInfo as BaseModel;
use app\yiban\model\Sports as SportsModel;
/**
 * ID:6001 运动会：长安大学运动会报名
 * 测试易班账号 ID: PW:
 * @package app\yiban\home
 */
class Sports extends Api
{
	public function _initialize()
    {

    	parent::_initialize();
    	$this->token = $this->getToken($this->verifyRequest);
    }

    public function init(){

		$checkData = $this->checkParams();
		if($checkData['status'] !== true){
			return json($checkData);
		}

        $stuInfo = $this->getStudentInfo($this->token);
        
        if($stuInfo != false){
            $model = new BaseModel;
            $stuBaseInfo = $model->getBaseInfoById($stuInfo->yb_studentid);

            $this->initData['status'] = true;
            $personalInitData['yb_realname'] = $stuInfo->yb_realname;
            $personalInitData['yb_id'] = $stuInfo->yb_userid;
            $personalInitData['stu_id'] = $stuInfo->yb_studentid;
            $personalInitData['class_id'] = $stuBaseInfo['class'];
            $personalInitData['sex'] = $stuBaseInfo['sex'];
            $personalInitData['mobile'] = $stuBaseInfo['mobile'];
            $personalInitData['head_img'] = $stuInfo->yb_userhead;

            $this->initData['personal'] = $personalInitData;

            $model = new SportsModel;
            $sportsList = $model->getSportsListById($stuInfo->yb_studentid);
            if(count($sportsList)){
            	$this->initData['sports']['status'] = true;
            	$this->initData['sports']['events'] = $sportsList;
        	}else{
        		$this->initData['sports']['status'] = false;
            	$this->initData['sports']['events'] = $sportsList;
        	}

            return json($this->initData);
        }else{
            $data['status'] = 'error';
            $data['code'] = '0x2004';
            $data['info'] = '无法获取易班信息，可能是未通过校方认证';
            return json($data);
        }
    	
    }

    public function submit(){
    	$token = $this->getToken($this->verifyRequest);
        
        //申请校级权限后打开
        $stu_id = $this->getStudentId($token);

    	$form_data = $_POST['formarray'];

    	if(empty($form_data)){
    		//请求数据不合法
    		$data['status'] = 'error';
    		$data['code'] = '0x6001';
    		$data['info'] = '参数非法';
    		return json($data);
    	}

    	$dataSize = count($form_data);

    	//由于下标固定，所以放到这里，更新手机号码
    	if($form_data[2]['name'] == 'tel' && preg_match("/^1[34578]{1}\d{9}$/",$form_data[2]['value'])){
    		$model = new BaseModel;
    		$model -> updateMobileById($stu_id,$form_data[2]['value']);
    	}

    	$insert_data = array();
    	$j=0;
    	for($i=3;$i<$dataSize-1;$i=$i+2){

    		//更新报名信息
    		if($form_data[$i]['name'] == 'type' && $form_data[$i+1]['name'] == 'events'){
    			$insert_data[$j]['stu_id'] = $stu_id;
    			$insert_data[$j]['type_id'] = $form_data[$i]['value'];
    			$insert_data[$j]['event_id'] = $form_data[$i+1]['value'];
    			$insert_data[$j]['status'] = 1;
    		}
    		$j++;
    	}

    	$model = new SportsModel;
		
		if($model -> submit($insert_data)){
			$data['status'] = 'success';
			$data['info'] = '报名成功';
		}else{
			$data['status'] = 'error';
    		$data['info'] = '签到失败';
    		$data['code'] = '0x6003';
		}
		return json($data);
    }

}