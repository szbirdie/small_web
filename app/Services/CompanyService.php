<?php
namespace App\Services;

use App\Models\Company;
use Illuminate\Http\Request;

/**
 * 公司service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class CompanyService
{
    public $company;

    /**
     * 注入company 对象实例
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * 获取公司信息
     * @param $where
     * @param array $field
     * @return array
     */
    public function getOne($where,$field=[])
    {
        if($field){
            $data = $this->company->where($where)->get($field)->first();
        }else{
            $data = $this->company->where($where)->get()->first();
        }

        if($data){
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 用作后台创建用户选择公司
     * @param Request $request
     * @return object
     */
    public function getCompanys(Request $request){
        $q = $request->get('q');
        return $this->company::where('company_name', 'like', "%$q%")->paginate(null, ['id', 'company_name as text']);
    }

    /**
     * 获取税务信息
     * @param $id
     * @return mixed
     */
    public function getCompanyTaxInfo($id){
        return $this->company::where('id', $id)->get()->toArray();
    }
    
    /**
     * 软删除
     * @param int $id
     * @return \Eloquent
     */
    public function del(int $id){
        $data = $this->company->where('id',$id)->first();
        $data->state = 2;
        return $data->save();
    }
    
}

