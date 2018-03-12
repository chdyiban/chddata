<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

use app\yiban\model\Score as ScoreModel;
/**
 * yiban管理后台
 * @package yiban/admin/score
 */
class Score extends Admin
{
	public function config(){
       return $this->moduleConfig();
    }
    public function index(){
        $order = $this->getOrder();
        $map = $this->getMap();

        $model = new ScoreModel;
        // 数据列表
        $data_list = $model->paginate();

        return ZBuilder::make('table')
            ->setPageTitle('期末成绩')
            ->setSearch(['stu_id' => '学号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['stu_id','学号'],
                ['KCMC','课程名称'],
                ['XF','学分'],
                ['QMCJ', '期末成绩'],
                ['PSCJ', '平时成绩'],
                ['ZZ','最终成绩']
            ])
            ->setRowList($data_list) // 设置表格数据
            ->fetch(); // 渲染模板
    }

    public function translate(){
        $page = input('get.page');
        if($page == ''){
            $page = 0;
        }
    	$result = Db::name('yiban_index')->page($page,20)->select();
        // dump($result);
    	if(count($result)){
    		$model = new ScoreModel;
    		$list = array();
    		foreach ($result as $key => $value) {
    			$list[$key]['stu_id'] = $value['stu_id'];
    			$scoreArray = json_decode($value['score'],true);
                dump($scoreArray);

                
                foreach ($scoreArray as $k => $v) {

                    $list[$key]['XNXQ'] = $v[0]['val'];
                    $list[$key]['KCDM'] = $v[1]['val'];
                    $list[$key]['KCXH'] = $v[2]['val'];
                    $list[$key]['KCMC'] = $v[3]['val'];
                    $list[$key]['KCLB'] = $v[4]['val'];
                    $list[$key]['XF'] = $v[5]['val'];
                    $list[$key]['QMCJ'] = $v[6]['val'];
                    $list[$key]['PSCJ'] = $v[7]['val'];
                    $list[$key]['ZPCJ'] = $v[8]['val'];

                    switch (count($v)) {
                        case '10':
                            $list[$key]['ZPCJ'] = $v[7]['val'];
                            $list[$key]['ZZ'] = $v[8]['val'];
                            $list[$key]['JD'] = $v[9]['val'];
                            break;
                        case '11':
                            $list[$key]['ZZ'] = $v[9]['val'];
                            $list[$key]['JD'] = $v[10]['val'];
                            break;
                        case '12':
                            $list[$key]['SYCJ'] = $v[9]['val'];
                            $list[$key]['ZZ'] = $v[10]['val'];
                            $list[$key]['JD'] = $v[11]['val'];
                            break;
                        case '13':
                            //覆盖
                            $list[$key]['QMCJ'] = $v[7]['val'];
                            $list[$key]['PSCJ'] = $v[8]['val'];
                            $list[$key]['ZPCJ'] = $v[9]['val'];
                            $list[$key]['ZZ'] = $v[10]['val'];
                            $list[$key]['JD'] = $v[11]['val'];
                        default:
                            # code...
                            break;
                    }
                }
                // $model->saveAll($list);
    		}
            $page ++;
            //echo '<script>window.location = "http://localhost/chddata/public/admin.php/yiban/score/index.html?page='.$page.'"</script>';
    	}
    }
}