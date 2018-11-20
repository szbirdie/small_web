<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderPay;
use App\Models\Province;
use App\Models\City;
use App\Models\Area;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UserLevelGoods;
use App\Models\OrderGoods;
use App\Models\Goods;
use App\Models\LogOrderOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Log;

/**
 * 订单service
 */
class OrderService
{
    public $order;
	public $order_pay;
	public $province;
	public $city;
	public $area;
    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {
        $this->order = new Order;
        $this->order_pay = new OrderPay;
		$this->province = new Province;
		$this->city = new City;
		$this->area = new Area;
		$this->user = new User;
		$this->user_level_goods = new UserLevelGoods;
		$this->user_level = new UserLevel;
		$this->order_goods = new OrderGoods;
		$this->goods = new Goods;
		$this->log_order_operation = new LogOrderOperation;
    }

    /**
     * 添加订单支付凭证表
     * @author lvqing@kuaigang.net
     * @param   $data 添加的参数
     * @param $request
     */
    public function createOrderPay($data){
    	return $this->order_pay->create($data);
    }

    /**
     * 根据条件查看支付凭证表
     * @author lvqing@kuaigang.net
     * @param   $data 添加的参数
     * @param $request
     */
    public function getOrderPayWithWhere($where){
        return $this->order_pay->where($where)->first();
    }

    /**
     * 修改支付凭证表的字段
     * @author lvqing@kuaigang.net
     * @param   $data 添加的参数
     * @param   $where 条件参数
     * @param $request
     */
    public function updateOrderPay($data, $where){
        return $this->order_pay->where($where)->update($data);
    }

    /**
     * 修改父类订单表的字段
     * @author lvqing@kuaigang.net
     * @param   $data 添加的参数
     * @param   $where 条件参数
     * @param $request
     */
    public function updateOrder($data, $where){
    	return $this->order->where($where)->update($data);
    }


    /**
     * 获取省列表
     * @param $request
     */
    public function getProvinceList()
    {
//        $province_list = CacheService::getCache(config('cache.cache_name.province_list'));
//        if (empty($province_list)) {
            $province_list = $this->province->select(['id', 'name'])->orderBy('id', 'asc')->get()->toArray();
//            $province_list_json = json_encode($province_list);
//            CacheService::setCache(config('cache.cache_name.province_list'),$province_list_json,10);
//        }

        return $province_list;
    }

    /**
     * 获取城市列表
     * @param   $where 条件参数
     * @param $request
     */
    public function getCityList($where)
    {
//        $city_list = CacheService::getCache(config('cache.cache_name.city_list'));

//        if (empty($city_list)) {
            $city_list = $this->city->select(['id', 'name'])->where($where)->orderBy('id', 'asc')->get()->toArray();
//            $city_list_json = json_encode($city_list);
//            CacheService::setCache(config('cache.cache_name.city_list'),$city_list_json,10);
//        }

        return $city_list;
    }

    /**
     * 获取区域地址
     * @param   $where 条件参数
     * @param $request
     */
    public function getDistrictList($where)
    {
//        $district_list = CacheService::getCache(config('cache.cache_name.district_list'));
//        if (empty($district_list)) {
            $district_list = $this->area->select(['id', 'name'])->where($where)->orderBy('id', 'asc')->get()->toArray();
//            $district_list_json = json_encode($district_list);
//            CacheService::setCache(config('cache.cache_name.district_list'),$district_list_json,10);
//        }
//
        return $district_list;
    }

    //获取所有省份城市信息
    // 张闯 2018 -0928
    public function allCity(){


        $cachename='allCity_List';

        $allCity_List = CacheService::getCache($cachename);

        if (empty($allCity_List)) {

            $data['province_list'] = $this->province->select(['id', 'name'])->orderBy('id', 'asc')->get()->toArray();
            $data['city_list'] = $this->city->select(['id', 'name','province_id as parent_id'])->orderBy('id', 'asc')->get()->toArray();
            $data['district_list'] = $this->area->select(['id', 'name','city_id as parent_id'])->orderBy('id', 'asc')->get()->toArray();

            CacheService::setCache($cachename,json_encode($data),60*24*30);//缓存一个月

        }else{

            $data= json_decode($allCity_List,true);
        }

        return $data;


    }


    /**
     * 获取订单列表
     * @param $userId
     * @param $companyId
     * @param string $orderStatus
     * @param int $num
     * @return mixed
     */
    public function getList_new($userId,$companyId,$request='',$num=10){


        $user_id = $userId;
        $company_id = $companyId;
        $order_status = $request['order_status']??'';

//var_dump($user_id,$company_id);die();
        //new 前端 1带确认 -2代付款 -3待收货 -5已完成

        //不拆单
        if(empty($order_status) || $order_status==1 || $order_status==2){


            $select = array('orders.*');
            if($order_status){
                $where=array();

                switch ($order_status){
                    case 1:   //待确认
                        $where=array(0,1);
                        break;
                    case 2 : //待付款
                        $where=array(2);
                }
                $order_goods_data = $this->order
                        ->where('orders.company_id',$company_id)
                        ->whereIn('orders.order_status',$where)
                        ->select($select)
                        ->orderBy('orders.order_confirm','desc')
                        ->orderBy('orders.updated_at','desc')
                        ->paginate($num);

            }else{//全部订单

                $order_goods_data = $this->order
                    ->where('orders.company_id',$company_id)
                    ->select($select)
                    ->orderBy('orders.order_confirm','desc')
                    ->orderBy('orders.updated_at','desc')
                    ->paginate($num);
            }

            $data = array();
            $order = json_decode(json_encode($order_goods_data),true);
            $order_goods_data = $order['data'];

            if(!empty($order_goods_data)) {

                foreach ($order_goods_data as &$v) {

                    $select = array('order_goods.*',
                        'goods.specs_name','goods.materials_name','goods.labels','goods.big_categorys_name','goods.small_categorys_name',
                        'factorys.thumb as thumb');

                    $order_goods = $this->order_goods
                        ->join('goods', 'goods.id','=', 'order_goods.goods_id')
                        ->leftjoin('factorys', 'factorys.id','=', 'goods.factorys_id')
                        ->select($select)
                        ->where('order_id',$v['id'])
                        ->get();

                    $v['order_goods'] = $order_goods;
                }
                $data = $order_goods_data;
            }


        }else if($order_status==3 || $order_status==5){ // 拆单

                $where=array();

                switch ($order_status){
                    case 3:
                        $where=array(3,4);
                        break;
                    case 5:
                        $where=array(5);
                }

                $select = array('order_goods.*');
                $order_goods_data = $this->order_goods
                        ->join('orders', 'order_goods.order_id','=', 'orders.id')
                        ->where('orders.company_id',$company_id)
                        ->whereIn('order_goods.order_status',$where)
                        ->select($select)
                        ->orderBy('order_goods.updated_at','desc')
                        ->paginate($num);


                $data = array();
                $order = json_decode(json_encode($order_goods_data),true);
                $order_goods_data = $order['data'];

                if(!empty($order_goods_data)) {

                    $temp2 = [];
                    $order_all= [];
    //                            var_dump($order_goods_data);die();
                    foreach ($order_goods_data as $o_g_data) {
                        //订单总表
                        $order_all = $this->order->where('id',$o_g_data['order_id'])->first();
                        $order_all = json_decode(json_encode($order_all),true);
                        $order_all = $order_all;
                        $ginfo = $this->goods->where('id', $o_g_data['goods_id'])->first();
                        $storeinfo = DB::table('factorys')->where('id', $ginfo['factorys_id'])->first();

                        if ($storeinfo) {
                            $o_g_data['thumb'] = $storeinfo->thumb;
                        } else {
                            $o_g_data['thumb'] = '';
                        }
                        $o_g_data['specs_name'] = $ginfo['specs_name'];
                        $o_g_data['materials_name'] = $ginfo['materials_name'];
                        $o_g_data['labels'] = $ginfo['labels'];
                        $o_g_data['big_categorys_name'] = $ginfo['big_categorys_name'];
                        $o_g_data['small_categorys_name'] = $ginfo['small_categorys_name'];

                        if ($o_g_data['order_status'] == 3 || $o_g_data['order_status'] == 4 || $o_g_data['order_status'] == 5) {
                            $temp1 = [];
                            $temp1[] = $o_g_data;
                            $order_all['order_goods'] = $temp1;

                        } else {
                            $temp2[] = $o_g_data;
                            $order_all['order_goods'] = $temp2;
                        }
                        $data[] = $order_all;
                    }
                }

        }


        $order['data'] = $data;

        return $order;


    }


    /**
     * 获取订单列表
     * @author zhao
     * @param $request
     * @return array|bool
     */
    public function getList($request){
        $user_id = $request->user_id;
        $company_id = $request->company_id;
        $order_status = $request->order_status ?? '';
        $num = $request->num ?? 10;
        if($order_status == 3 || $order_status == 4 || $order_status == 5 || !$order_status){
            $order_data_i = $this->order->where('company_id',$company_id)->paginate($num);
        }else{
            $order_data_i = $this->order->where('company_id',$company_id)->where('order_status',$order_status)->paginate($num);
        }


        $order = json_decode(json_encode($order_data_i),true);
        $order_data = $order['data'];
        if(!$order_data){
            return [];
        }


        $data = array();
        foreach($order_data as $key => $o_data){
            if($order_status){
                if($order_status == 1){
                    $order_goods_data = $this->order_goods->where('order_id', $o_data['id'])->where('order_no', $o_data['order_no'])->whereIn('order_status',array(0,1))->get()->toArray();
                }elseif($order_status == 3){
                    $order_goods_data = $this->order_goods->where('order_id', $o_data['id'])->where('order_no', $o_data['order_no'])->whereIn('order_status',array(3,4))->get()->toArray();
                }else{
                    $order_goods_data = $this->order_goods->where('order_id', $o_data['id'])->where('order_no', $o_data['order_no'])->where('order_status',$order_status)->get()->toArray();
                }
            }else{
                $order_goods_data = $this->order_goods->where('order_id', $o_data['id'])->where('order_no', $o_data['order_no'])->get()->toArray();
            }


            if(!empty($order_goods_data)) {

                $ii=0;
                $temp2 = [];
                foreach ($order_goods_data as $o_g_data) {

                    $ginfo = $this->goods->where('id', $o_g_data['goods_id'])->first();


                    $temp2 = [];
                    if ($order_goods_data) {
                        foreach ($order_goods_data as $o_g_data) {
                            $ginfo = $this->goods->where('id', $o_g_data['goods_id'])->first();


                            $storeinfo = DB::table('factorys')->where('id', $ginfo['factorys_id'])->first();

                            if ($storeinfo) {
                                $o_g_data['storehouses_log'] = $storeinfo->thumb;
                            } else {
                                $o_g_data['storehouses_log'] = '';
                            }
                            $o_g_data['specs_name'] = $ginfo['specs_name'];
                            $o_g_data['materials_name'] = $ginfo['materials_name'];
                            $o_g_data['labels'] = $ginfo['labels'];
                            $o_g_data['big_categorys_name'] = $ginfo['big_categorys_name'];
                            $o_g_data['small_categorys_name'] = $ginfo['small_categorys_name'];

                            if ($o_g_data['order_status'] == 3 || $o_g_data['order_status'] == 4 || $o_g_data['order_status'] == 5) {
                                $temp1 = [];
                                $temp1[] = $o_g_data;
                                $o_data['order_goods'] = $temp1;

                            } else {
                                $temp2[] = $o_g_data;
                                $o_data['order_goods'] = $temp2;
                            }
                        }


                        $data[] = $o_data;

                    }
                }

            }

//            var_dump($data);die();
        }
//        var_dump($data);die();
        $order['data'] = $data;

        return $order;
    }


    /**
     * 获取订单详情
     * @param $userId
     * @param $orderId
     * @param $orderNo
     * @param $companyId
     * @param $orderStatus
     * @return array|bool
     */
    public function getDetails($userId,$orderId,$orderNo,$companyId,$orderStatus){
        $user_id  = $userId;
        $order_id = $orderId;
        $order_no = $orderNo;
        $company_id = $companyId;
        $order_status = $orderStatus;
        $order_data = $this->order->where('id',$order_id)->where('order_no',$order_no)->where('company_id',$company_id)->get()->toArray();
        if(!$order_data){
            return false;
        }
        $data = array();
        foreach($order_data as $key => $o_data){
            $order_goods_data = $this->order_goods->where('order_id', $o_data['id'])->where('order_no', $o_data['order_no'])->get()->toArray();

            if(!empty($order_goods_data)){

                foreach($order_goods_data as $o_g_data){
                    $ginfo = $this->goods->where('id',$o_g_data['goods_id'])->first();
                    $storeinfo =   DB::table('factorys')->where('id', $ginfo['factorys_id'])->first();
                    $storehouses = DB::table('storehouses')->where('id', $ginfo['storehouses_id'])->first();
                    $order_pay = DB::table('order_pay')->where('order_id', $o_data['id'])->first();

                    if(!empty($order_pay)){

                        $o_g_data['order_pay_info'] = $order_pay;

                    }else{
                        $o_g_data['order_pay_info'] = '';
                    }
                    $o_g_data['storehouses'] = $storehouses;

                    $o_g_data['factorys_address'] = $storeinfo->address;
                    $o_g_data['factorys_thumb'] = $storeinfo->thumb;
                    $o_g_data['specs_name'] = $ginfo['specs_name'];
                    $o_g_data['materials_name'] = $ginfo['materials_name'];
                    $o_g_data['labels'] = $ginfo['labels'];
                    $o_g_data['big_categorys_name'] = $ginfo['big_categorys_name'];
                    $o_g_data['small_categorys_name'] = $ginfo['small_categorys_name'];


                    $temp1 = [];
                    $temp1[] = $o_g_data;
                    $o_data['order_goods'] = $temp1;

                    if($o_data['area_name_path']){
                        $areaL = explode("_", $o_data['area_name_path']);
                        $province_name = '';$city_name = '';$area_name = '';
                        if(isset($areaL[0])){
                            $province = $this->province->where('id',$areaL[0])->first();
                            $province_name = $province->name??'';
                        }
                        if(isset($areaL[1])){
                            $city = $this->city->where('id',$areaL[1])->first();
                            $city_name = $city->name??'';
                        }
                        if(isset($areaL[2])){
                            $area = $this->area->where('id',$areaL[2])->first();
                            $area_name = $area->name??'';
                        }

                        $o_data['area_name_path'] = $province_name.$city_name.$area_name.$o_data['consignee_address'];
                    }
                    $data[] = $o_data;

                }

            }


        }
        return $data;

    }

    /**
     * 订单确认
     * @author zhao
     * @param $user_id
     * @param $order_id
     * @param $order_no
     * @param $company_id
     * @return bool
     * @throws \Exception
     */
    public function confirm($user_id,$order_id,$order_no,$company_id)
    {
        $order_info = $this->order->select()->where('id', $order_id)->where('order_no', $order_no)->where('company_id', $company_id)->where('order_status',1)->first();
        if(!$order_info){
            return false;
        }
        $order_status = $order_info->order_status;
        DB::beginTransaction();
        $res1 = $this->order->where('id', $order_id)->where('order_no', $order_no)->where('company_id', $company_id)->update(array('order_status' => 2));
        $res4 = $this->order_goods->where('order_no', $order_no)->update(array('order_status' => 2));
        $gids = $this->order_goods->select('goods_id','weight')->where('order_no', $order_no)->where('order_id', $order_id)->get()->toArray();
        $user_level_info = $this->user->select('user_level_id')->where('id',$user_id)->first();
        if(is_null($user_level_info)){
            return false;
        }
        $user_level_id = $user_level_info->user_level_id;
//        foreach($gids as $gid){
//            $level_goods_weight = $this->user_level_goods->select('goods_lock_weight','goods_weight')->where('user_level_id',$user_level_id)->where('goods_id',$gid['goods_id'])->first();
//            if(is_null($level_goods_weight)){
//                return false;
//            }
//            $weight1 = bcsub($level_goods_weight->goods_lock_weight,$gid['weight']);
//            $weight2 = bcsub($level_goods_weight->goods_weight,$gid['weight']);
//            $res2 = $this->user_level_goods->where('user_level_id',$user_level_id)->where('goods_id',$gid['goods_id'])->update(array('goods_lock_weight'=>$weight1,'goods_weight'=>$weight2));
//            $goods_weight = $this->goods->select('weight')->where('id',$gid['goods_id'])->first()->weight;
//            $weight3 = bcsub($goods_weight,$gid['weight']);
//            $res3 = $this->goods->where('id',$gid['goods_id'])->update(array('weight'=>$weight3));
//        }
//        if ($res1 && $res2 && $res3 && $res4) {
        if ($res1 && $res4) {
            $status_path = $order_status.'|'.'2';
            $data = [
                'order_id'=>$company_id,
                'order_no'=>$order_no,
                'type'=>4,
                'op_user_id'=>$user_id,
                'order_status_path'=>$status_path,
                'company_id'=>$company_id,
            ];
            $this->log_order_operation->insert($data);
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }

    }

    /**
     * 取消订单
     * @author zhao
     * @param $user_id
     * @param $order_id
     * @param $order_no
     * @param $company_id
     * @return bool
     * @throws \Exception
     */
    public function cancel($user_id,$order_id,$order_no,$company_id){
        $order_info = $this->order->select()->where('id', $order_id)->where('order_no', $order_no)->where('company_id', $company_id)->where('order_status',1)->first();
        if(!$order_info){
            return false;
        }
        $order_status = $order_info->order_status;
        DB::beginTransaction();
        $res1 = $this->order->where('id', $order_id)->update(array('order_status' => -1));
        $res_order = $this->order_goods->where('order_id', $order_id)->update(array('order_status' => -1));
        $gids = $this->order_goods->select('goods_id','weight')->where('order_no', $order_no)->where('order_id', $order_id)->get()->toArray();
        $user_level_info = $this->user->select('user_level_id')->where('id',$user_id)->first();
        if(!$user_level_info){
            return false;
        }
        $user_level_id = $user_level_info->user_level_id;
        foreach($gids as $gid){
            $level_goods_weight = $this->user_level_goods->select('goods_weight','goods_lock_weight')->where('user_level_id',$user_level_id)->where('goods_id',$gid['goods_id'])->first();
            if(!$level_goods_weight){
                return false;
            }
//            $weight1 = bcadd($level_goods_weight->goods_weight,$gid['weight']);
            $weight2 = bcsub($level_goods_weight->goods_lock_weight,$gid['weight']);
            $res2 = $this->user_level_goods->where('user_level_id',$user_level_id)->where('goods_id',$gid['goods_id'])->update(array('goods_lock_weight'=>$weight2));
            $goods_weight = $this->goods->select('weight')->where('id',$gid['goods_id'])->first()->weight;
            $weight3 = bcadd($goods_weight,$gid['weight']);
            $res3 = $this->goods->where('id',$gid['goods_id'])->update(array('weight'=>$weight3));
        }
        if ($res1 && $res2 && $res3 && $res_order) {
            $status_path = $order_status.'|'.'-1';
            $data = [
                'order_id'=>$company_id,
                'order_no'=>$order_no,
                'type'=>4,
                'op_user_id'=>$user_id,
                'order_status_path'=>$status_path,
                'company_id'=>$company_id,
            ];
            $this->log_order_operation->insert($data);
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }
    }

    /**
     * 确认收货
     * @author zhao
     * @param $user_id
     * @param $order_id
     * @param $order_no
     * @param $company_id
     * @param $goods_id
     * @return bool
     * @throws \Exception
     */
    public function collect_confirm($user_id,$order_id,$order_no,$company_id,$goods_id){
        $order_info = $this->order_goods->select()->where('order_id', $order_id)->where('order_no', $order_no)->first();

        if(!$order_info){
            return false;
        }

        $order_status = $order_info->order_status;
        DB::beginTransaction();
        $res1 = $this->order_goods->where('order_id', $order_id)->where('order_no', $order_no)->where('goods_id',$goods_id)->update(array('order_status' => 5));
        $order_status_array = $this->order_goods->select('order_status','order_id')->where('order_no', $order_no)->get()->toArray();
        $array = array_unique($order_status_array,SORT_REGULAR);
        if(!isset($array[1]) && ($array[0]['order_status'] == 5)){
            $res2 = $this->order->where('id', $array[0]['order_id'])->where('order_no', $order_no)->update(array('order_status' => 5));
        }else{
            $res2 = 1;
        }

        $gids = $this->order_goods->select('goods_id','weight')->where('order_no', $order_no)->where('order_id', $order_id)->where('goods_id',$goods_id)->first();
        $user_level_info = $this->user->select('user_level_id')->where('id',$user_id)->first();
        if(!$user_level_info){
            return false;
        }
        $user_level_id = $user_level_info->user_level_id;

        $level_goods_weight = $this->user_level_goods->select('goods_lock_weight','goods_weight')->where('user_level_id',$user_level_id)->where('goods_id',$goods_id)->first();
        if(is_null($level_goods_weight)){
            return false;
        }
        $weight1 = bcsub($level_goods_weight->goods_lock_weight,$gids->weight);
        $weight2 = bcsub($level_goods_weight->goods_weight,$gids->weight);
        $res3 = $this->user_level_goods->where('user_level_id',$user_level_id)->where('goods_id',$goods_id)->update(array('goods_lock_weight'=>$weight1,'goods_weight'=>$weight2));
        $goods_weight = $this->goods->select('weight')->where('id',$goods_id)->first()->weight;
        $weight3 = bcsub($goods_weight,$gids->weight);
        $res4 = $this->goods->where('id',$goods_id)->update(array('weight'=>$weight3));

        if ($res1 && $res2 && $res3 && $res4) {
            $status_path = $order_status.'|'.'5';
            $data = [
                'order_id'=>$order_id,
                'order_no'=>$order_no,
                'type'=>4,
                'op_user_id'=>$user_id,
                'order_status_path'=>$status_path,
                'company_id'=>$company_id,
            ];
            $this->log_order_operation->insert($data);
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }
    }


    /**
     * 获取销售对象订单列表
     * @author lvqing
     * @param $request
     * @return array|bool
     */
    public function getMarketList(){
        $order_list = $this->order->where('order_status',1)->get()->toArray();
        return $order_list;
    }


    /**
     * 待确认 提交确认
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function confirmOrder(Request $request){
        $order_id = $request->get('order_id');
        $order_info = $this->order->where(['id' => $order_id])->update(['order_status'=>1, 'order_confirm'=>2]);
        return $order_info;
    }
    /**
     * 待确认 取消订单
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function cancelOrder(Request $request){
        $order_id = $request->get('order_id');
        $res = $this->order->find($order_id);
        $order_info = $this->order->where(['id' => $order_id])->update(['order_status'=>-1]);
        $this->log_order_operation->insert(['order_id'=>$order_id,'order_no'=>$res['order_no'],'type'=>5,'op_admin_id'=>Admin::user()->id,'op_mark'=>'取消订单','order_status_path'=>$res['order_status'].'|-1']);
        return $order_info;
    }

    /**
     * 待支付 完成支付
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successPayment(Request $request){
        $order_id = $request->get('order_id');
        $order_pay_id = $request->get('order_pay_id');
        $description = $request->get('description');
        $res = $this->order->find($order_id);
        $order_info = $this->order_goods->where(['order_id' => $order_id])->first();
        switch ($order_info['logistics_type']) {
            case '1':
                $order_info = $this->order->where(['id' => $order_id])->update(['order_status'=>3]);
                $this->log_order_operation->insert(['order_id'=>$order_id,'order_no'=>$res['order_no'],'type'=>5,'op_admin_id'=>Admin::user()->id,'op_mark'=>'完成支付','order_status_path'=>'2|3']);
                break;
            case '2':
                $order_info = $this->order->where(['id' => $order_id])->update(['order_status'=>4]);
                $this->log_order_operation->insert(['order_id'=>$order_id,'order_no'=>$res['order_no'],'type'=>5,'op_admin_id'=>Admin::user()->id,'op_mark'=>'完成支付','order_status_path'=>'2|4']);
                break;
        }

        if ($order_info == 1) {
            $order_pay_info = $this->order_pay->find($order_pay_id);
            if ($order_pay_info['description'] == '') {
               $description_add = $description . '|' . date('Y-m-d', time());
            }else{
                $description_add = $order_pay_info['description'] . ',' . $description . '|' . date('Y-m-d', time());
            }
            $order_pay_info = $order_pay_info->update(['description' => $description_add,'pay_price' => DB::raw('pay_price +'.$description*100)]);
            $this->order->where(['id' => $order_id])->update(['pay_price' => DB::raw('pay_price +'.$description*100)]);
            return $order_pay_info;
        }else{
            return 0;
        }
    }

    /**
     * 待发货 确认发货
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successConsignment(Request $request){
        $order_id = $request->get('order_id');
        $res = $this->order->find($order_id);
        $order_info = $this->order->where(['id' => $order_id])->update(['order_status'=>4]);
        $this->log_order_operation->insert(['order_id'=>$order_id,'order_no'=>$res['order_no'],'type'=>5,'op_admin_id'=>Admin::user()->id,'op_mark'=>'取消订单','order_status_path'=>$res['order_status'].'|4']);
        return $order_info;
    }

    /**
     * 后台待支付 审核失败
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function auditingError(Request $request){
        $order_pay_id = $request->get('order_pay_id');
        $description = $request->get('description');
        $order_id = $request->get('order_id');
        $order_pay_info = $this->order_pay->where(['id' => $order_pay_id])->update(['state'=>3, 'description' => $description]);
        if (!empty($order_pay_info)) {
            $order_info = $this->order->where(['id' => $order_id])->update(['order_payment'=>2]);
            return $order_info;
        }else{
            return 0;
        }

    }

    /**
     * 后台待发货 保存支付凭证备注信息
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function saveOrderPayInfo(Request $request){
        $order_pay_id = $request->get('order_pay_id');
        $description = $request->get('description');
        $order_pay_info = $this->order_pay->find($order_pay_id);
        $res = $this->order->find($order_pay_info['order_id']);
        if ($order_pay_info['description'] == '') {
           $description_add = $description . '|' . date('Y-m-d', time());
        }else{
            $description_add = $order_pay_info['description'] . ',' . $description . '|' . date('Y-m-d', time());
        }
        $order_pay_info->update(['description' => $description_add,'pay_price' => DB::raw('pay_price +'.$description*100)]);
        $this->order->where(['id' => $order_pay_info['order_id']])->update(['pay_price' => DB::raw('pay_price +'.$description*100)]);
        $this->log_order_operation->insert(['order_id'=>$res['id'],'order_no'=>$res['order_no'],'type'=>6,'op_admin_id'=>Admin::user()->id,'op_mark'=>'添加支付金额'.$description*100]);
        return $res;
    }

    /**
     * 订单定时释放回调函数
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function orderRelease(Request $request){
        //运行时关闭
        \Debugbar::disable();
        $allString = $request->key;
        $searchString = "release:";
        $newString = strstr($allString, $searchString);
        $length = strlen($searchString);
        $id =  substr($newString, $length);
        $oder_id_list = explode(',', $id);
        Log::info('orderService_oder_id_list',['info'=>$oder_id_list]);
        foreach ($oder_id_list as $k => $val) {
            $order_list = Order::select('users.user_level_id', 'order_goods.goods_id', 'order_goods.weight')
                        ->where(['orders.id'=>$val, 'orders.order_status'=>1])
                        ->join('users','orders.user_id','=','users.id')
                        ->join('order_goods','orders.id','=','order_goods.order_id')
                        ->get()
                        ->toArray();
            if (!empty($order_list)) {
                //更新等级商品表
                foreach ($order_list as $key => $value) {
                    try {
                        //释放锁定库存
                        $res = UserLevelGoods::where(['user_level_id'=>$value['user_level_id'], 'goods_id'=>$value['goods_id']])->update(array(
                           'goods_lock_weight' => DB::raw('goods_lock_weight -'.$value['weight'])
                        ));
                    } catch (\Exception $e) {
                        ShowApi(1000,'','系统内部异常');
                    }
                }

                try {
                    Order::where(['id'=>$val])->update(['order_status'=>'-1']);
                    OrderGoods::where(['order_id'=>$val])->update(['order_status'=>'-1']);
                } catch (\Exception $e) {
                    ShowApi(1000,'','系统内部异常');
                }
            }else{
                return json_encode(array('code' => 201, 'message' => '数据库操作失败', 'result' => array()));
            }
        }
        return json_encode(array('code' => 200, 'message' => 'OK', 'result' => array()));
    }

    /**
     * 订单定时收货回调函数
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function orderReceive(Request $request){
        //运行时关闭
        \Debugbar::disable();
        $allString = $request->key;
        $searchString = "receive:";
        $newString = strstr($allString, $searchString);
        $length = strlen($searchString);
        $id =  substr($newString, $length);
        $oder_id_list = explode(',', $id);
        foreach ($oder_id_list as $k => $val) {
            try {
                Order::where(['id'=>$val])->update(['order_status'=>5, 'done_type'=>2]);
                OrderGoods::where(['order_id'=>$val])->update(['order_status'=>5]);
            } catch (\Exception $e) {
                ShowApi(1000,'','系统内部异常');
            }
        }
        return json_encode(array('code' => 200, 'message' => 'OK', 'result' => array()));

    }


    /**
     * 支付已提交的凭证列表
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function pay_info($param,$userinfo){

        $where=array(
            'order_id'=>$param['order_id'], //显示默认地址
        );

        $res =  DB::table('order_pay')
            ->where($where)
            ->select('*')
            ->get();

//        var_dump($res);die();

        foreach ($res as $v){
            $v->pay_certificate=explode(',',$v->pay_certificate);
        }
        return $res;

    }

}

