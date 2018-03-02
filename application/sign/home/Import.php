<?php
namespace app\sign\home;

use app\index\controller\Home;
use think\helper\Hash;
use think\Request;
use app\user\model\User as UserModel;
use think\Db;
/**
 * 批量上传数据
 * @package app\sign\home
 */
class Import extends Home
{
	public function index(){
		// $i = Request::instance()->param('id');
		$i = 0;
		set_time_limit(0);
		$UserModel = new UserModel;
		$file = fopen('/Users/yang/Downloads/临时导入数据 (2).csv','r'); 
		while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
		//print_r($data); //此为一个数组，要获得每一个数据，访问数组下标即可
			//1.工号修正，补充首位0
			if(strlen($data[1]) == 4){
				$data[1] = '00'.$data[1];
			}
			// //2.密码补充，采用系统加密方式，密码初始值为工号
			// $data[3] = Hash::make((string)$data[1]);

			 $people_list[] = $data;
			// break;
			$dataAll[] = array(
				'username' => $data[1],
				'nickname' => $data[2],
				'password' => Hash::make((string)$data[1]),
				'mobile' => $data[6],
				'role' => $data[11],
				'group' => '0',
				'sigup_ip' => '0',
				'create_time' => time(),
				'update_time' => '0',
				'sort' => '100',
				'status' => '1'
			);

		}
		$UserModel->saveAll($dataAll);
	}

	public function checkPwd(){
		$id = '005972';
		$pwd = Hash::make((string)$id);
		echo $pwd.'<br/>';
		$checkPwd = Hash::check((string)$id, $pwd);
		// Hash::check((string)$password, $user->password)
		echo $checkPwd;
	}

	public function updatePwd(){
		set_time_limit(0);
		$startId = 4;
		$countTrue = 0;
		$countFalse = 0;
		while ($startId <= 118) {
			$username = Db::table('dp_admin_user')->where('id',$startId)->value('username');
			$password = Hash::make((string)$username);
			if(Hash::check((string)$username, $password)){
				Db::table('dp_admin_user')->where('id',$startId)->setField('password', $password);
				echo 'success';
				$countTrue++;
			}else{
				echo 'error';
				$countFalse++;
			}
			$startId++;
		}		
	}
}