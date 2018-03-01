<?php
namespace app\yiban\home;

use app\index\controller\Home;
use think\Log;
use think\Db;
use think\Session;
// use yiban\YBOpenApi;
// use util\CurlAutoLogin;
// use util\simple_html_dom;

use app\yiban\model\Face as FaceModel;
/**
 * 易班首页控制器
 * @package app\yiban\home
 */
class Index extends Home
{
	private $url_real_me = 'https://openapi.yiban.cn/user/real_me';

	public function _initialize(){
    }

    public function index(){

    }

    public function JsonTxt2Mysql(){
        $content = file_get_contents('/Applications/MAMP/htdocs/DolphinPHP/app/yundonghui/female.json');
        $result = json_decode($content,true);

        $insertData = array();
        $i = 0;

        foreach ($result['events'] as $key => $value) {
            //为女性定制
            if($value['id']>47){
                # code...
                switch ($value['typeId']) {
                    case '0':
                        $insertData[$i]['event_name'] = '田径项目';
                        break;
                    case '1':
                        $insertData[$i]['event_name'] = '《国家学生体质健康标准》测试项目';
                        break;
                    case '2':
                        $insertData[$i]['event_name'] = '趣味项目';
                        break;
                    
                    default:
                        # code...
                        break;
                }
                $insertData[$i]['event_id'] = $value['id'];
                $insertData[$i]['type_id'] = $value['typeId'];
                $insertData[$i]['type_name'] = $value['eventsName'];
                $insertData[$i]['detail_a'] = $value['detail']['a'];
                $insertData[$i]['detail_b'] = $value['detail']['b'];
                $insertData[$i]['detail_c'] = $value['detail']['c'];

                $i++;
            }

        }
        dump($insertData);
        $re = Db::name('sports_list2018')->insertAll($insertData);
        dump($re);
    }


}