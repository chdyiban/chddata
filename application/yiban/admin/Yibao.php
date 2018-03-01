<?php
namespace app\yiban\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder; // 引入ZBuilder
use think\Db;

use app\user\model\Role as RowModel;

/**
 * yibao管理后台
 * @package app\yibao\admin
 */
class Yibao extends Admin
{

    public function config(){
       return $this->moduleConfig();
    }

    public function index(){
    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$map['insured_sex_code'] = ['>','0'];
    	$data_list = Db::name('yiban_base_info')->where($map)->order($order)->paginate();

        return ZBuilder::make('table')
            ->setTableName('yiban_base_info')
            ->hideCheckbox()
			->addColumns([
				['name', '姓名','', '', '', 'text-center'],
				['college','学院','','','','text-center'],
				['id_card_num','身份证号','text.edit', '', '', 'text-center'],
                ['insured_sex_code','性别代码','text.edit', '', '', 'text-center'],
                ['insured_nation_code','民族代码','', '', '', 'text-center'],
                ['birthday','出生日期','text.edit', '', '', 'text-center'],
                ['insured_date','参保日期','', '', '', 'text-center'],
                ['number','学籍编号','', '', '', 'text-center'],
                ['length_of_schooling','学制代码','text.edit', '', '', 'text-center'],
                ['class_name','班级名称','text.edit', '', '', 'text-center'],
                ['class','班级编号','', '', '', 'text-center'],
                ['major','所学专业','', '', '', 'text-center'],
                ['mobile','个人电话','text.edit', '', '', 'text-center'],
                ['special_code','特殊情况代码','text.edit', '', '', 'text-center'],
                ['domicile','户籍所在地','text.edit', '', '', 'text-center'],
                ['home_address','家庭住址','text.edit', '', '', 'text-center'],
                ['contact_person','家庭联系人','text.edit', '', '', 'text-center'],
                ['contact_person_mobile','家庭联系人电话','text.edit', '', '', 'text-center']
			])
        	->setPageTitle('医保信息报送单')
        	->setSearch(['name' => '姓名','number'=>'学号'])
        	->setRowList($data_list) // 设置表格数据
        	->addOrder('id') // 添加排序
        	->addFilter('college,class_name')
        	->fetch();
    }

    public function undo(){
    	$roleName = Db::name('admin_role')->where('id', session('user_auth.role'))->value('name');
    	$order = $this->getOrder();
    	$map = $this->getMap();
    	$map['insured_sex_code'] = '0';
    	if(session('user_auth.role') != 1){
    		$map['college'] = $roleName;
    	}

    	$data_list = Db::name('yiban_base_info')->where($map)->order($order)->paginate();

    	return ZBuilder::make('table')
            ->setTableName('yiban_base_info')
            ->hideCheckbox()
			->addColumns([
				['name', '姓名','', '', '', 'text-center'],
				['college','学院','','','','text-center'],
                ['class','班级编号','', '', '', 'text-center'],
                ['major','所学专业','', '', '', 'text-center'],
                ['number','学号','','','text-center']
			])
        	->setPageTitle($roleName.'医保信息未报送名单')
        	->setSearch(['name' => '姓名','number'=>'学号'])
        	->setRowList($data_list) // 设置表格数据
        	->addOrder('class') // 添加排序
        	->addFilter('college,class,major')
        	->fetch();
    }
}