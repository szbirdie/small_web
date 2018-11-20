<?php
namespace App\Services;


use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\AdminConfigService;

/**
 * 购物车service
 * @author zhang chuang
 * @version 1.0.0
 */
class CartService
{


    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {

    }





    /*
     * 购物车 zhang chuang
     * user_level_goods.goods_lock_weight 锁定库存
     * user_level_goods.inventory 库存
     * unit_price 单价
     *
     */

    public function cart($userinfo){


        $res=array();

        //同一公司，下属不同用户可能存在不同用户等级 产品吴佳杰确认9-13
        $user_level=$this->user_level($userinfo['user_id']);


        $cart_where=array(

            'carts.company_id'=>$userinfo['company_id'],
            'user_level_goods.user_level_id'=>$user_level
        );


        //查询对应商品等级库存量

        $cartlist = DB::table('carts')
                           ->Join('user_level_goods', 'user_level_goods.goods_id','=', 'carts.goods_id')
                           ->Join('factorys', 'factorys.id','=', 'carts.factorys_id')
                           ->where($cart_where)
                           ->select('carts.*','factorys.thumb','user_level_goods.goods_weight as inventory','user_level_goods.goods_lock_weight','user_level_goods.price as unit_price')
                           ->orderBy('carts.id','desc')
                           ->get();


        $res_cartlist = $this->goods_price_inventory($cartlist);

        $res['cartlist']=    $res_cartlist['cartlist'];
        $res['total_price']= $res_cartlist['total_price'];
        $res['checked_num']= $res_cartlist['checked_num'];


        $res['tip'] = AdminConfigService::getConfig('cart_tip'); // 调用

        return $res;


    }


    //加入购物车 zhang chuang

    public function add_cart($userinfo,$param){



        check_param($param); //检测参数

        //查询商品
        $goods = DB::table('goods')->where('id',$param['goods_id'])->first();
//        var_dump($goods);die();

        if(empty($goods)){

            ShowApi(0,'','goods_id 不存在');
        }

        //用户当前 可以购买某商品的数量
        $now_can_buy_goods_weight = $this->check_user_can_buy_weight($userinfo['user_id'],$param['goods_id']);



        //查询购物车
        $where=array(
            'company_id'=>$userinfo['company_id'],
            'goods_id'=>$param['goods_id']
        );


        $user_cart = DB::table('carts')->where($where)->first();

        if(!empty($user_cart)){

            //购物车存在商品时 （需要购买的数量= 购物车数量+用户需要加入购物车的数量）
            $user_need_goods_weight =  $user_cart->goods_weight + $param['goods_weight'];

            if($user_need_goods_weight > $now_can_buy_goods_weight){

                ShowApi(0,'','库存不足');
            }


            $insert_data=array(
                'goods_weight'=> $user_need_goods_weight,//用户需要加入购物车的数量
                'updated_at'=>date('Y-m-d H:i:s',time()),
            );

            $res=DB::table('carts')
                ->where($where)
                ->update($insert_data);


        }else{

            //购物车不存在商品时 （需要购买的数量 + 用户需要加入购物车的数量）

            if($param['goods_weight']> $now_can_buy_goods_weight){

                ShowApi(0,'','库存不足');
            }


            $insert_data=array(
                'user_id'=>$userinfo['user_id'],
                'company_id'=>$userinfo['company_id'],
                'goods_id'=>$goods->id,
                'goods_name'=>$goods->name,
                'goods_weight'=>$param['goods_weight'],//用户输入重量
                'categorys_id'=>$goods->big_categorys_id,
                'categorys_name'=>$goods->big_categorys_name,
                'specs_id'=>$goods->specs_id,
                'specs_name'=>$goods->specs_name,
                'materials_id'=>$goods->materials_id,
                'materials_name'=>$goods->materials_name,
                'storehouses_id'=>$goods->storehouses_id,
                'storehouses_name'=>$goods->storehouses_name,
                'factorys_id'=>$goods->factorys_id,
                'factorys_name'=>$goods->factorys_name,
                'created_at'=>date('Y-m-d H:i:s',time()),
                'updated_at'=>date('Y-m-d H:i:s',time()),
                'checked'=>1,
                'state'=>1,
                'small_categorys_id'=>   $goods->small_categorys_id,
                'small_categorys_name'=> $goods->small_categorys_name,

            );

            $res=DB::table('carts')->insert($insert_data);

        }


        if($res){
            return true;
        }else{
            return false;
        }



    }


    //返回 用户当前 可以购买某商品的数量 zhang chuang

    public function check_user_can_buy_weight($userid,$goodsid){


        //查询用户等级
        $user_level_id = $this->user_level($userid);

        //查询对应用户等级商品库存情况

        $where_goods_level=array(
            'goods_id'=> $goodsid,
            'user_level_id'=>$user_level_id

        );


        $goods_level = DB::table('user_level_goods')->where($where_goods_level)->first();

        if(empty($goods_level)){

            ShowApi(0,'','user_level_goods 获取失败');
        }

        //当前等级可以购买的商品数量
        $now_can_buy_goods_weight= $goods_level->goods_weight - $goods_level->goods_lock_weight;

        return $now_can_buy_goods_weight;





    }



    // 返回 用户等级 zhang chuang
    // 同一公司，下属不同用户可能存在不同用户等级 产品吴佳杰确认 9-13 16:50

    public function user_level($userid){


        //查询用户等级

        $user_level = DB::table('users')->where('id',$userid)->first();

        return $user_level->user_level_id;



    }


    //购物车数量改变 zhang chuang

    public function cart_num_change($userinfo,$param){


        check_param($param); //检测参数

        //查询购物车
        $where=array(
            'id'=>$param['cart_id']
        );

        $user_cart = DB::table('carts')->where($where)->first();


        if(empty($user_cart)){

            ShowApi(0,'','cart_id 不存在');
        }



        if(!empty($user_cart)){

            //用户当前 可以购买某商品的数量
            $now_can_buy_goods_weight= $this->check_user_can_buy_weight($userinfo['user_id'],$user_cart->goods_id);

            //用户需要购买的购物车的数量
            $user_need_goods_weight =   $param['goods_weight'];

            if($user_need_goods_weight > $now_can_buy_goods_weight){

                ShowApi(0,'','库存不足');
            }


            $insert_data=array(
                'goods_weight'=> $user_need_goods_weight,//用户需要加入购物车的数量
                'updated_at'=>date('Y-m-d H:i:s',time()),
            );

            $res=DB::table('carts')
                ->where($where)
                ->update($insert_data);

            if($res){
                return true;
            }else{
                return false;
            }
        }





    }


    //购物车选中/未选中/全选 zhang chuang


    public function cart_checked($userinfo,$param){


        check_param($param); //检测参数

        $where_update=array();
        $data=array();

        $where_type=1;

        switch ($param['checked_type']){

            case 1: //单选

                $where_update=array(
                    'id'=>intval($param['cart_id'])
                );

                $user_cart = DB::table('carts')->where($where_update)->first();

                if(empty($user_cart)){

                    ShowApi(0,'','cart_id 不存在');
                }

                $checked=1;
                if($user_cart->checked==1){
                    $checked=0;
                }

                $data['checked']=$checked;

                break;

            case 2: //全选
                $where_update=array(
                    'company_id'=>$userinfo['company_id']
                );

                $data['checked']=1;

                break;

            case 3: //全不选

                $where_update=array(
                    'company_id'=>$userinfo['company_id']
                );

                $data['checked']=0;

                break;

            case 4: //小类 全选

                $where_update=  explode(',',$param['cart_id']);

                $user_cart = DB::table('carts')->whereIn('id',$where_update)->first();


                if(empty($user_cart)){

                    ShowApi(0,'','cart_id 不存在');
                }

                $data['checked']=1;

                $where_type=2;
                break;

            case 5: //小类 全不选

                $where_update=  explode(',',$param['cart_id']);

                $user_cart = DB::table('carts')->whereIn('id',$where_update)->first();

                if(empty($user_cart)){

                    ShowApi(0,'','cart_id 不存在');
                }

                $data['checked']=0;

                $where_type=2;
                break;


        }


        if(empty($where_update) || empty($data)){

            ShowApi(0,'','更新条件出错！');
        }

        if($where_type==1){

            $res=  DB::table('carts')->where($where_update)->update($data);

        }else{

            $res = DB::table('carts')->whereIn('id',$where_update)->update($data);

        }

        if($res){
            return true;
        }else{
            return false;
        }




    }


    //购物车删除 商品 zhang chuang
    public function cart_del($userinfo,$param){



            check_param($param); //检测参数


            $del_cart_id = explode(',',$param['cart_id']);

            $where=array();
            if(is_array($del_cart_id)){

                foreach ($del_cart_id as $v){

                    $where[]= intval($v);
                }


            }else{
                ShowApi(0,'','cart_id 出错');
            }

            if(empty($where)){
                ShowApi(0,'','cart_id 出错');
            }


            $res=DB::table('carts')
                    ->whereIn('id',$where)
                    ->delete();

            if($res){
                 return true;
            }else{
                 return false;
            }



    }


    //验证是否休市 zhang chuang

    public function market_status(){


            $res = AdminConfigService::getConfig('market_status'); // 调用

            if($res){
                return true;
            }else{
                return false;
            }


    }

    //订单提交时 商品信息，地址信息等 zhang chuang

    public function order_info($userinfo){

            $res=array();

            $where=array(
                'user_address.company_id'=>$userinfo['company_id'],
                'user_address.is_default'=>2, //显示默认地址
                'user_address.state'=>1 //显示默认地址
            );
            //用户默认地址信息
            $res['user_address'] =  DB::table('user_address')
                ->leftJoin('provinces', 'provinces.id','=', 'user_address.province')
                ->leftJoin('cities', 'cities.id','=', 'user_address.city')
                ->leftJoin('areas', 'areas.id','=', 'user_address.area')
                ->select(['user_address.*','provinces.name as province_name','cities.name as city_name','areas.name as area_name'])
                ->where($where)
                ->first();

            if(!empty($res['user_address'])){

                $res['user_address']->consignee_address=$res['user_address']->province_name.$res['user_address']->city_name.$res['user_address']->area_name.$res['user_address']->consignee_address;

            }



            //同一公司，下属不同用户可能存在不同用户等级 产品吴佳杰确认9-13
            $user_level=$this->user_level($userinfo['user_id']);

            $cart_where=array(
                'carts.checked'=>1,
                'carts.company_id'=>$userinfo['company_id'],
                'user_level_goods.user_level_id'=>$user_level
            );

            //查询对应商品等级库存量

            $cartlist = DB::table('carts')
                ->Join('user_level_goods', 'user_level_goods.goods_id','=', 'carts.goods_id') // 等级库存
                ->leftJoin('factorys', 'factorys.id','=', 'carts.factorys_id')
                ->leftJoin('storehouses', 'storehouses.id','=', 'carts.storehouses_id')           // 仓库地址信息等
                ->where($cart_where)
                ->select(
                    'carts.*',
                    'factorys.thumb',
                    'storehouses.address as storehouses_address','storehouses.relator as storehouses_relator','storehouses.relator_phone as storehouses_relator_phone',
                    'user_level_goods.goods_weight as inventory','user_level_goods.goods_lock_weight','user_level_goods.price as unit_price'
                    )
                ->orderBy('carts.id','desc')
                ->get();


            $res_cartlist = $this->goods_price_inventory($cartlist);

            $res['cartlist']=    $res_cartlist['cartlist'];
            $res['total_price']= $res_cartlist['total_price'];

            return $res;



    }

    //计算单价与总价 和 实际库存数量 zhangchuang

    public function goods_price_inventory($cartlist){

            $res=array();
            $total_price=0;

            $checked_num=0;

            foreach ($cartlist as $v){

                $v->price= $v->unit_price * $v->goods_weight/1000; // 计算单个商品价格=（每吨）单价*每吨价格（goods_weight/1000）
                // 计算选中商品总价
                if($v->checked==1){
                    $total_price+=$v->price; //商品总价
                }

                //单价 总价
                $v->unit_price = $v->unit_price; //单价 由分转化 元
                $v->price = $v->price; //单价 由分转化 元


                //结果保留两位小数
                $v->unit_price = number_format($v->unit_price, 2, '.', '');
                $v->price= number_format($v->price, 2, '.', '');


                //库存 单位kg //库存 单位吨 Tons（吨）
                $v->total_inventory = $v->inventory; //总库存 包含锁定
                $v->total_inventory_Tons = $v->inventory/1000; //总库存 包含锁定

                $v->inventory= $v->inventory - $v->goods_lock_weight; //减去锁定剩余库存 计算：当前等级总库存-锁定库存
                $v->inventory_Tons= $v->inventory/1000; //减去锁定剩余库存 计算：当前等级总库存-锁定库存


                if($v->checked==1){
                    $checked_num++;
                }

            }

            $total_price = $total_price; //单价 由分转化 元
            $total_price = number_format($total_price, 2, '.', '');

            $res['cartlist']=$cartlist;
            $res['total_price']= $total_price;
            $res['checked_num']= $checked_num;


            return $res;

    }


    /*
     *
     *  订单提交接口
     *
     *  流程
     *
     *  1.检测库存，锁定库存
     *  2.生成订单
     *  3.生成订单商品表
     *
     *  9-17 zhang chuang
     *
     */

    public function order_submit($userinfo,$param){


            if($param['logistics_type']==1){ //卖家承运

                //收货人信息
                $consignee= DB::table('user_address')->where('id',$param['address_id'])->where('state',1)->first();

                if(empty($consignee)){

                    ShowApi(0,'','收货地址不存在'); //收货地址不存在
                }

            }



            //DB::connection()->enableQueryLog();

            $Transaction_is_succcess = true; //事务中sql是否全部执行成功，有失败时 赋值为false


            try {

                //手动开始事务
                DB::beginTransaction();


                $buy_goods_list =$this->cart($userinfo);

                $buy_goods = array(); //购买的商品列表
                $buy_goods_total_price=  $buy_goods_list['total_price'];; //购买的商品总价
                $buy_goods_total_weight= 0; //购买的总重量

                $buy_goods_list =$buy_goods_list['cartlist'];

                //var_dump($buy_goods_list);exit();

                //检测库存
                foreach ($buy_goods_list as $v){

                    if($v->checked==1){ //判断选中商品库存

                        if($v->goods_weight > $v->inventory){  //用户购买数量大于库存（总库存-锁定库存）

                            ShowApi(0,'',$v->goods_name.'库存不足'); //返回给用户某种商品库存不足
                        }

                        $buy_goods[]=$v;
                        $buy_goods_total_weight= $buy_goods_total_weight+$v->goods_weight;

                    }


                }

                // 创建时间 保持统一
                $cteate_time=date('Y-m-d H:i:s',time());

                // 1.锁定库存


                $user_level=$this->user_level($userinfo['user_id']);


                foreach ($buy_goods as $v){

                    $where_update=array(
                        'goods_id'=>      $v->goods_id,
                        'user_level_id'=> $user_level,
                    );

                    $data=array(
                        'goods_lock_weight'=>$v->goods_lock_weight + $v->goods_weight //已锁定库存 加上 用户欲购买库存
                    );

                    $temp_res=DB::table('user_level_goods')->where($where_update)->update($data);

                    if(!$temp_res){
                        $Transaction_is_succcess=false;
                    }

                }




                //2.生成订单数据

                $order_no=$this->create_order_no();

                if($param['logistics_type']==2){ //自提

                    $insert_data=array(
                        'user_id'=>   $userinfo['user_id'],
                        'company_id'=>$userinfo['company_id'],
                        'order_status'=>1, //默认提交后是0
                        'order_no'=>$order_no,
                        'total_price'=>$buy_goods_total_price,//由 元 转 分
                        'order_goods'=>json_encode($buy_goods),
                        'order_src'=>  $userinfo['request_type'],
                        'created_at'=> $cteate_time,
                        'total_weight'=>$buy_goods_total_weight
                    );


                }else{ //卖家配送

                    $insert_data=array(
                        'user_id'=>   $userinfo['user_id'],
                        'company_id'=>$userinfo['company_id'],
                        'order_status'=>1, //默认提交后是0
                        'order_no'=>$order_no,
                        'total_price'=>$buy_goods_total_price,//由 元 转 分
                        'order_goods'=>json_encode($buy_goods),
                        'order_src'=>  $userinfo['request_type'],
                        'consignee_name'=> $consignee->consignee_name,
                        'consignee_address'=>$consignee->consignee_address,
                        'consignee_phone'=>$consignee->consignee_phone,
                        'area_id'=>$consignee->area,//最后一级区域id
                        'area_name_path'=>$consignee->province.'_'.$consignee->city.'_'.$consignee->area,
                        'created_at'=> $cteate_time,
                        'total_weight'=>$buy_goods_total_weight

                    );

                }





                $res_order_id= DB::table('orders')->insertGetId($insert_data);

                if(!$res_order_id){

                    $Transaction_is_succcess=false;

                }


                //3.生成订单商品数据

                $insert_data=array();

                $i=0;
                foreach ($buy_goods as $v){

                    $insert_data[$i]['order_id'] =   $res_order_id;
                    $insert_data[$i]['goods_id'] =   $v->goods_id;
                    $insert_data[$i]['goods_name'] = $v->goods_name;
                    $insert_data[$i]['weight'] = $v->goods_weight;
                    $insert_data[$i]['price'] =  $v->unit_price; // 单价/ 每吨/价格
                    $insert_data[$i]['total_price'] = $v->price;// 小计单件总价 单价*重量（吨）
                    $insert_data[$i]['order_no'] = $order_no;
                    $insert_data[$i]['state'] = 1;
                    $insert_data[$i]['created_at'] = $cteate_time;
                    $insert_data[$i]['storehouses_id'] =   $v->storehouses_id;
                    $insert_data[$i]['storehouses_name'] = $v->storehouses_name;
                    $insert_data[$i]['goods_info'] = json_encode($v);
                    $insert_data[$i]['logistics_type'] = $param['logistics_type'];
                    $i++;
                }



                $order_goods_id= DB::table('order_goods')->insert($insert_data);


                if(!$order_goods_id){

                    $Transaction_is_succcess=false;

                }

                //$Transaction_is_succcess; //所有数据库操作是否成功
                //4.成功提交事务 否则 回滚

                if($Transaction_is_succcess){ //订单提交成功

                    $users= DB::table('users')->where('id',$userinfo['user_id'])->first();
                    SMS_MESSAGE(1,'SMS_005',$users->relator_phone,$order_no);

                    //sleep(20);

                    DB::commit();

                    $data_return=array(
                        'order_id'=>$res_order_id,
                        'order_no'=>$order_no,
                        'status'=>true
                    );

                }else{

                    DB::rollBack();
                    $data_return=array(
                        'order_id'=>'',
                        'order_no'=>'',
                        'status'=>false
                    );


                }

                return $data_return;



            } catch (\Exception $e) { //异常回滚


                DB::rollBack();
                return false;
            }



    }



    //创建唯一订单号

    public function create_order_no(){


            $order= date('ymdHis',time());
            $ms= microtime(true);
            $ms = $ms *10000;
            $ms=substr($ms,-6);

            return $order.$ms;



    }











}

