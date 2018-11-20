<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/13
 * Time: 13:59
 */

namespace App\Services;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressService
{
    public $userAddress;

    /**
     * 注入UserAddress 对象实例
     */
    public function __construct()
    {
        $this->userAddress = new UserAddress;
    }

    /**
     * 保存卸货地址数据
     * @param $param
     * @return mixed
     */
    public function save($param){
        if(isset($param['user_address_id'])){
            $id = $param['user_address_id'];
            unset($param['user_address_id']);
        }
        DB::beginTransaction();
        if($param['type'] == 'mod'){
            unset($param['type']);
            if(!isset($id)){
                return 2;
            }
            $res = $this->userAddress::where('id',$id)->update($param);
            if($res){
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        }elseif ($param['type'] == 'add'){
            $res = $this->userAddress->where('company_id',$param['company_id'])->where('user_id',$param['user_id'])->where('consignee_address',$param['consignee_address'])->get()->toArray();
            if($res){
                return 4;
            }
            unset($param['type']);
            $res = $this->userAddress::insert($param);
            if($res){
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        }else{
            return 3;
        }
    }

    /**
     * 获取卸货地址列表数据
     * @param $request
     * @author zhao
     * @return fixed
     */
    public function getList($companyId,$num){
        if(!$num){
            $num = 10;
        }
        return $this->userAddress->where('company_id',$companyId)
                    ->leftJoin('provinces', 'provinces.id','=', 'user_address.province')
                    ->leftJoin('cities', 'cities.id','=', 'user_address.city')
                    ->leftJoin('areas', 'areas.id','=', 'user_address.area')
                    ->select(['user_address.*','provinces.name as province_name','cities.name as city_name','areas.name as area_name'])
                    ->orderBy('id', 'desc')->paginate($num);
    }

    /**
     * 获取地址信息
     * @author zhao
     * @param $request
     * @return fixed
     */
    public function getInfo($userAddressId,$companyId){
        return $this->userAddress->where('id',$userAddressId)->where('company_id',$companyId)->first();
    }

    /**
     * 卸货地址设为默认
     * @param $userAddressId
     * @param $companyId
     * @return bool
     */
    public function setDefault($userAddressId,$companyId){
        DB::beginTransaction();
        $res = $this->userAddress->where('company_id',$companyId)->update(array('is_default'=>1));
        if($res){
            $res = $this->userAddress->where('id',$userAddressId)->update(array('is_default'=>2));
            if($res){
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        }else{
            return $res;
        }
    }

    /**
     * 删除卸货地址
     * @author zhao
     * @param Request $request
     * @return mixed
     */
    public function del($userAddressId,$companyId){
        DB::beginTransaction();
        $res = $this->userAddress->where('id',$userAddressId)->where('company_id',$companyId)->update(array('state'=>2));
        if($res){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }
    }

}