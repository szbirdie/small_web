<?php
namespace App\Services;

use App\Models\CompanyRejectLog;
use App\Models\CompanyTemporary;
use Illuminate\Http\Request;

/**
 * 公司零时表service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class CompanyTemporaryService
{
    public $companyTemporary;
    public $companyRejectLog;
    /**
     * 注入company 对象实例
     */
    public function __construct( )
    {
        $this->companyTemporary = new CompanyTemporary();
        $this->companyRejectLog = new CompanyRejectLog();
    }

    /**
     * 获取公司临时表信息
     */
    public function getOne($where,$field=[])
    {
        if($field){
            $data = $this->companyTemporary->where($where)->get($field)->first();
        }else{
            $data = $this->companyTemporary->where($where)->get()->first();
        }
        if($data){
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 获取单条用户驳回日志
     * @param $where
     * @param array $orderBy
     * @return mixed
     */
    public function getRejectLogOne($where,$orderBy=[])
    {
        $model = $this->companyRejectLog->where($where);
        if($orderBy){
            $model->orderBy($orderBy[0],$orderBy[1]);
        }
        $data = $model->get()->first();
        if($data){
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 保存公司信息
     * @param $userId
     * @param $data
     */
    public function save($userId,$data)
    {
        $model = $this->companyTemporary->where(['user_id'=>$userId])->get()->first();
        if($model){
            $row = $model->update($data);
        }else{
            $row = $this->companyTemporary->insertGetId($data);
        }
        return $row;
    }

    
    /**
     * 软删除
     * @param int $id
     * @return \Eloquent
     */
    public function del(int $id){
        $data = $this->companyTemporary->where(['id'=>$id])->first();
        $data->state = 2;
        return $data->save();
    }
    
}

