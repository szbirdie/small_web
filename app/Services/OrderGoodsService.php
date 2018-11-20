<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/12
 * Time: 16:29
 */

namespace App\Services;

use App\Models\Order;
use App\Models\OrderGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Models\Logistic;
use App\Models\UserLevelGoods;
use App\Services\CacheService;
use App\Models\LogOrderOperation;
use App\Models\User;
use Encore\Admin\Facades\Admin;

class OrderGoodsService
{
    public $order_goods;
    public $order;
    public $goods;

    /**
     * 注入order_goods 对象实例
     * @param
     */
    public function __construct()
    {
        $this->order_goods = new OrderGoods;
        $this->order = new Order;
        $this->goods = new Goods;
    }

    public function getOrderInfo($id,$no){
        return $this->order_goods::where('order_id', $id)->where('order_no',$no)->get()->toArray();
    }

    public function insertInfo($data){
        return $this->order_goods::insert($data);
    }

    public function getLogGoods($company_id,$num){
        $cache_key='recommend_log_goods'.$company_id;

        $res = CacheService::getCache($cache_key);
        if(!empty($res)){
            $res= json_decode($res,true);
            return $res;
        }else {
            $order_info = DB::table('order_goods as o_g')
                ->Join('orders as o', function ($join) {
                    $join->on('o_g.order_id', '=', 'o.id');
                })
                ->where('o.company_id', $company_id)
                ->where('o.order_status', 5)
                ->orderBy('o.updated_at', 'DESC')
                ->get()->toArray();

            foreach ($order_info as $item) {
                $res = DB::table('goods as g')
                    ->leftJoin('user_level_goods as u_l_o', function ($join) {
                        $join->on('g.id', '=', 'u_l_o.goods_id');
                    })
                    ->where('g.id', $item->goods_id)
                    ->select('g.*', 'u_l_o.*')->get()->toArray();
                $res[0]->inventory = bcsub($res[0]->goods_weight,$res[0]->goods_lock_weight);
                if ($res) {
                    $array[$item->goods_id] = json_encode($res[0]);
                }

            }
            $res = array_slice($array, 0, $num);
            if ($res) {
                CacheService::setCache($cache_key, json_encode($res),60);
                return $res;
            } else {
                return [];
            }
        }
    }
    /**
     * 待确认跟新订单商品表
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function updateOrderGoods(Request $request){
        $goods_id = $request->get('goods_id');
        $old_goods_id = $request->get('old_goods_id');
        $order_id = $request->get('order_id');
        $weight = $request->get('weight');
        $price = $request->get('price');
        $order_goods_id = $request->get('order_goods_id');
        if (empty($goods_id) || empty($old_goods_id) || empty($order_id) || empty($weight) || empty($price) || empty($order_goods_id)) {
            return 0;
        }
        
        $goods_info = Goods::find($goods_id);
        $data = array();
        $data['goods_id'] = $goods_id;
        $data['goods_name'] = $goods_info['small_categorys_name'];
        $data['weight'] = $weight*1000;
        $data['price'] = $price*100;
        $data['total_price'] = $weight * $price * 100;
        $data['storehouses_id'] = $goods_info['storehouses_id'];
        $data['storehouses_name'] = $goods_info['storehouses_name'];
        $data['goods_info'] = json_encode($goods_info);
        $old_order_info = OrderGoods::find($order_goods_id);

        if (empty($old_order_info)) {
            return 0;
        }
        if ($old_order_info['order_status'] > '1') {
            return 0;
        }
        $order_info = OrderGoods::where(['id' => $order_goods_id])->update($data);
        if (empty($order_info)) {
            return 0;
        }        
        $res = Order::find($order_id);
        $user_info = User::find($res['user_id']);
        if (empty($user_info)) {
            return 0;
        }
               
        if ($order_info == 1) {
            $total_price = 0;
            $total_weight = 0;
            $order_goods_josn = array();
            $order_goods_list = OrderGoods::where(['order_id' => $order_id])->get();

            foreach ($order_goods_list as $key => $value) {
                $total_price += $value['total_price'];
                $total_weight += $value['weight'];
                $order_goods_josn[] = Goods::find($value['goods_id']);
            }
            $order_goods = json_encode($order_goods_josn);
            Order::where(['id' => $order_id])->update(['total_price'=>$total_price, 
                                                        'total_weight'=>$total_weight, 
                                                        'order_goods'=>$order_goods]);
            UserLevelGoods::where(['goods_id' => $old_goods_id , 'user_level_id' =>$user_info['user_level_id']])
            ->update(['goods_lock_weight' => DB::raw('goods_lock_weight -'.$old_order_info['weight'])]);           
            UserLevelGoods::where(['goods_id' => $goods_id , 'user_level_id' =>$user_info['user_level_id']])
            ->update(['goods_lock_weight' => DB::raw('goods_lock_weight +'.$data['weight'])]);

            LogOrderOperation::insert(['order_id'=>$res['id'],
                'order_no'=>$res['order_no'],
                'type'=>7,
                'op_admin_id'=>Admin::user()->id,
                'op_mark'=>'替换订单商品',
                'goods_id_path'=>$old_goods_id.'|'.$goods_id,
                'ip'=>$request->getClientIp()
                ]);

        }

        return $order_info;
    }
    /**
     * 待确认删除订单商品表
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function delOrderGoods(Request $request){
        $order_goods_id = $request->get('order_goods_id');
        if (empty($order_goods_id)) {
            return false;
        }
        $res = OrderGoods::where(['order_goods.id'=>$order_goods_id])->join('orders','order_goods.order_id','=','orders.id')->first();

        LogOrderOperation::insert(['order_id'=>$res['id'],
            'order_no'=>$res['order_no'],
            'type'=>7,
            'op_admin_id'=>Admin::user()->id,
            'op_mark'=>'删除订单商品',
            'goods_id_path'=>$order_goods_id.'|0',
            'ip'=>$request->getClientIp()
            ]);
        $order_info = OrderGoods::where(['id' => $order_goods_id])->delete();
        return $order_info;
    }
    /**
     * 待确认 提交确认
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function confirmOrder(Request $request){
        $order_id = $request->get('order_id');
        $order_info = OrderGoods::where(['order_id' => $order_id])->update(['order_status'=>1]);
        return $order_info;
    }
    /**
     * 待确认 取消订单
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function cancelOrder(Request $request){
        $order_id = $request->get('order_id');
        $order_info_service = OrderGoods::where(['order_id' => $order_id]);
        $order_info = $order_info_service->update(['order_status'=>-1]);
        $order_goods_list = $order_info_service->get()->toArray();
        $res = Order::find($order_id);
        $user_info = User::find($res['user_id']);
        foreach ($order_goods_list as $key => $value) {
            UserLevelGoods::where(['goods_id' => $value['goods_id'] , 'user_level_id' =>$user_info['user_level_id']])
            ->update(['goods_lock_weight' => DB::raw('goods_lock_weight -'.$value['weight'])]);
        }

        return $order_info;
    }

    /**
     * 待支付 完成支付
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successPayment(Request $request){
        $order_id = $request->get('order_id');
        $order_info = OrderGoods::where(['order_id' => $order_id])->first();
        switch ($order_info['logistics_type']) {
            case '1':
                $order_info = OrderGoods::where(['order_id' => $order_id])->update(['order_status'=>3]);
                break;
            case '2':
                $order_info = OrderGoods::where(['order_id' => $order_id])->update(['order_status'=>4]);
                break;
        }
        return $order_info;
    }

    /**
     * 待发货 确认发货
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successConsignment(Request $request){
        $order_id = $request->get('order_id');
        $order_info = OrderGoods::where(['order_id' => $order_id])->update(['order_status'=>4]);
        return $order_info;
    }

    /**
     * 后台待发货 添加物流信息
     * @author lvqing@kuaigang.net
     * @param  string
     */
     public function  addLogistics(Request $request){
        $logistics_car_no = $request->get('logistics_car_no');
        $logistics_phone = $request->get('logistics_phone');
        $logistics_name = $request->get('logistics_name');
        $order_goods_id = $request->get('order_goods_id');
        $order_id = $request->get('order_id');
        $data = array();
        $data['car_no'] = $logistics_car_no;
        $data['phone'] = $logistics_phone;
        $data['name'] = $logistics_name;
        $data['order_goods_id'] = $order_goods_id;
        $data['order_id'] = $order_id;

        $logistic = Logistic::where(['order_id'=>$order_id, 'order_goods_id'=>$order_goods_id])->first();
        if ($logistic) {
            try {
                $insert = $logistic->update($data);
            } catch (\Exception $e) {
                $order_info = false;
            }

        }else{
            try {
                $insert = Logistic::insert($data);
            } catch (\Exception $e) {
                $order_info = false;
            }
        }
        
        
        $order_info = OrderGoods::where(['order_id'=>$order_id, 'goods_id'=>$order_goods_id])->update(['logistics_car_no'=>$logistics_car_no, 'logistics_phone'=>$logistics_phone, 'logistics_name'=>$logistics_name]);
        if (empty($order_info)) {
            $order_info = false;
        }
        return $order_info;

    }

    /**
     * 后台待确认 修改订单商品信息
     * @author lvqing@kuaigang.net
     * @param  string
    */
    public function updateOrderGoodsInfo(Request $request){
        $type = $request->get('type');
        $total_price = $request->get('total_price');
        $order_goods_price = $request->get('order_goods_price');
        $order_goods_weight = $request->get('order_goods_weight');
        $order_goods_id = $request->get('order_goods_id');
        if (empty($order_goods_id) || empty($type)) {
            return false;
        }
        // if (!empty($total_price)) {
        //     $total_price *= 100;
        // }
        // if (!empty($order_goods_price)) {
        //     $order_goods_price *= 100;
        // }
        // if (!empty($order_goods_weight)) {
        //     $order_goods_weight *= 1000;
        // }
        $order_goods_info = OrderGoods::find($order_goods_id);
        if (empty($order_goods_info)) {
            return false;
        }
        $res = Order::find($order_goods_info['order_id']);
        $user_info = User::find($res['user_id']);
        if (empty($user_info)) {
            return false;
        }        
        switch ($type) {
            case '1'://修改总价
                $price = $total_price/($order_goods_info['weight']/1000);
                $price *= 100;
                $total_price *= 100;
                $total_price_path = $order_goods_info['total_price'];
                $order_info = OrderGoods::where(['id' => $order_goods_id])->update(['total_price'=>$total_price, 'price'=>$price]);
                LogOrderOperation::insert(['order_id'=>$res['id'],
                    'order_no'=>$res['order_no'],
                    'type'=>7,
                    'op_admin_id'=>Admin::user()->id,
                    'op_mark'=>'修改订单商品总价',
                    'goods_id_path'=>$order_goods_info['goods_id'].'|'.$order_goods_info['goods_id'],
                    'total_price_path'=>$total_price_path.'|'.$total_price,
                    'ip'=>$request->getClientIp()
                    ]);
                break;
            case '2'://修改销售价
                $total_price = $order_goods_price*($order_goods_info['weight']/1000);
                $total_price *= 100;
                $order_goods_price *= 100;
                $price_path = $order_goods_price;
                $order_info = OrderGoods::where(['id' => $order_goods_id])->update(['total_price'=>$total_price, 'price'=>$order_goods_price]);
                LogOrderOperation::insert(['order_id'=>$res['id'],
                    'order_no'=>$res['order_no'],
                    'type'=>7,
                    'op_admin_id'=>Admin::user()->id,
                    'op_mark'=>'修改订单商品销售价',
                    'goods_id_path'=>$order_goods_info['goods_id'].'|'.$order_goods_info['goods_id'],
                    'price_path'=>$price_path.'|'.$order_goods_price,
                    'ip'=>$request->getClientIp()
                    ]);
                break;
            case '3'://修改购买库存量
                $total_price = ($order_goods_info['price']/100)*$order_goods_weight;
                $total_price *= 100;
                $order_goods_weight *= 1000;
                $weight_path = $order_goods_info['weight'];
                $order_info = OrderGoods::where(['id' => $order_goods_id])->update(['total_price'=>$total_price, 'weight'=>$order_goods_weight]);
                if ($weight_path > $order_goods_weight) {
                    $weight = $weight_path - $order_goods_weight;
                    UserLevelGoods::where(['goods_id' => $order_goods_info['goods_id'] , 'user_level_id' =>$user_info['user_level_id']])
                    ->update(['goods_lock_weight' => DB::raw('goods_lock_weight -'.$weight)]);
                    //修改order表的 total_weight
                    Order::where(['id' => $order_goods_info['order_id']])
                    ->update(['total_weight' => DB::raw('total_weight -'.$weight)]);                    
                }else{
                    $weight = $order_goods_weight - $weight_path;
                    UserLevelGoods::where(['goods_id' => $order_goods_info['goods_id'] , 'user_level_id' =>$user_info['user_level_id']])
                    ->update(['goods_lock_weight' => DB::raw('goods_lock_weight +'.$weight)]);
                    //修改order表的 total_weight
                    Order::where(['id' => $order_goods_info['order_id']])
                    ->update(['total_weight' => DB::raw('total_weight +'.$weight)]);                      
                }

                LogOrderOperation::insert(['order_id'=>$res['id'],
                    'order_no'=>$res['order_no'],
                    'type'=>7,
                    'op_admin_id'=>Admin::user()->id,
                    'op_mark'=>'修改订单商品购买库存量',
                    'goods_id_path'=>$order_goods_info['goods_id'].'|'.$order_goods_info['goods_id'],
                    'weight_path'=>$weight_path.'|'.$order_goods_weight,
                    'ip'=>$request->getClientIp()
                    ]);
                break;
        }
        return $order_info;

    }
}