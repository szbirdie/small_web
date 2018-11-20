<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CompanyService;
use App\Services\UserService;
use App\Services\OrderGoodsService;
use App\Services\OrderService;
use App\Services\AdminConfigService;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserLevelService;
use App\Models\UserLevel;
use Illuminate\Support\Facades\DB;

/**
 * 后台管理默认页面
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class ApiController extends Controller
{
    /**
     * 用作后台创建用户选择公司
     * @param Company $company
     * @param Request $request
     * @return object
     */
    public function getCompanys(Company $company, Request $request){
        try {
            $companyService = new CompanyService($company);
            return $companyService->getCompanys($request);
        } catch (\Exception $e) { //异常回滚
            return 0;
        } 
    }
    
    /**
     * 用作后台创建用户选择等级
     * @param UserLevel $userLevel
     * @param Request $request
     * @return object
     */
    public function getUserLevels(UserLevel $userLevel,Request $request){
        try {
            $userLevelService = new UserLevelService($userLevel); 
            return $userLevelService->getUserLevels($request);
        } catch (\Exception $e) { //异常回滚
            return 0;
        }        
    }
    /**
     * 用作后台订单选择用户
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function getUsers(Request $request){
        try {
            $userService = new UserService();
            return $userService->getUsers($request);
        } catch (\Exception $e) { //异常回滚
            return 0;
        }
    }    
    /**
     * 待确认跟新订单商品表
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function updateOrderGoods(Request $request){
        try {
            $orderGoodsService = new OrderGoodsService();
            return $orderGoodsService->updateOrderGoods($request);
        } catch (\Exception $e) { //异常回滚
            return 0;
        }        
    }
    /**
     * 待确认删除订单商品表
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function delOrderGoods(Request $request){
        try {
            $orderGoodsService = new OrderGoodsService();
            return $orderGoodsService->delOrderGoods($request);
        } catch (\Exception $e) { //异常回滚
            return 0;
        }
    }
    /**
     * 待确认 提交确认
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function confirmOrder(Request $request){
        try {

            $Transaction_is_succcess = true; //事务中sql是否全部执行成功，有失败时 赋值为false
            //手动开始事务
            DB::beginTransaction();
            $orderService = new OrderService();
            $order_info = $orderService->confirmOrder($request);
            if (empty($order_info)) {
                $Transaction_is_succcess=false;
            }
            
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->confirmOrder($request);
            if (empty($order_info)) {
                  $Transaction_is_succcess=false;
            }
            if($Transaction_is_succcess){
                $uinfo = DB::table('users')->select('relator_phone')
                    ->leftJoin('orders as o', function ($join) {
                        $join->on('users.id', '=', 'o.user_id');
                    })->where('o.id',$request->get('order_id'))->first();
                SMS_MESSAGE(1,'SMS_002',$uinfo->relator_phone);
                DB::commit();
                return 1;
            }else{
                DB::rollBack();
                return 0;
            }
        } catch (\Exception $e) { //异常回滚
            DB::rollBack();
            return 0;
        }
    }
    /**
     * 待确认 取消订单
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function cancelOrder(Request $request){
        try {
            $Transaction_is_succcess = true; //事务中sql是否全部执行成功，有失败时 赋值为false
            //手动开始事务
            DB::beginTransaction();
            $orderService = new OrderService();
            $order_info = $orderService->cancelOrder($request);
            if (empty($order_info)) {
                $Transaction_is_succcess=false;
            }
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->cancelOrder($request);
            // if (empty($order_info)) {
            //       $Transaction_is_succcess=false;
            // }
            if($Transaction_is_succcess){
                DB::commit();
                return 1;
            }else{
                DB::rollBack();
                return 0;
            }
        } catch (\Exception $e) { //异常回滚
            DB::rollBack();
            return 0;
        }            

    }

    /**
     * 待支付 完成支付
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successPayment(Request $request){
        try {
            $Transaction_is_succcess = true; //事务中sql是否全部执行成功，有失败时 赋值为false
            //手动开始事务
            DB::beginTransaction();
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->successPayment($request);
            if (empty($order_info)) {
                $Transaction_is_succcess=false;
            }
            $orderService = new OrderService();
            $order_info = $orderService->successPayment($request);
            if (empty($order_info)) {
                  $Transaction_is_succcess=false;
            }
            if($Transaction_is_succcess){
                DB::commit();
                return 1;
            }else{
                DB::rollBack();
                return 0;
            }
        } catch (\Exception $e) { //异常回滚
            DB::rollBack();
            return 0;
        } 

    }
    /**
     * 待发货 确认发货
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function successConsignment(Request $request){
        try {
            $Transaction_is_succcess = true; //事务中sql是否全部执行成功，有失败时 赋值为false
            //手动开始事务
            DB::beginTransaction();
            $orderService = new OrderService();
            $order_info = $orderService->successConsignment($request);
            if (empty($order_info)) {
                $Transaction_is_succcess=false;
            }
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->successConsignment($request);               
            if (empty($order_info)) {
                  $Transaction_is_succcess=false;
            }
            if($Transaction_is_succcess){
                $uinfo = DB::table('users')->select('relator_phone')
                    ->leftJoin('orders as o', function ($join) {
                        $join->on('users.id', '=', 'o.user_id');
                    })->where('o.id',$request->get('order_id'))->first();
                SMS_MESSAGE(1,'SMS_004',$uinfo->relator_phone);
                DB::commit();
                return 1;
            }else{
                DB::rollBack();
                return 0;
            }
        } catch (\Exception $e) { //异常回滚
            DB::rollBack();
            return 0;
        } 
    }
    /**
     * 后台待支付 审核失败
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function auditingError(Request $request){
        try {
            $orderService = new OrderService();
            $order_info = $orderService->auditingError($request);
            if (empty($order_info)) {
                return 0;
            }else{
                $uinfo = DB::table('users')->select('relator_phone')
                    ->leftJoin('orders as o', function ($join) {
                        $join->on('users.id', '=', 'o.user_id');
                    })->where('o.id',$request->get('order_id'))->first();
                SMS_MESSAGE(1,'SMS_003',$uinfo->relator_phone);
                return 1;
            }
        } catch (\Exception $e) { //异常回滚
            return 0;
        }             
    }

    /**
     * 后台待发货 保存支付凭证备注信息
     * @author lvqing@kuaigang.net
     * @return string
     */
    public function saveOrderPayInfo(Request $request){
        try {
            $orderService = new OrderService();
            $order_info = $orderService->saveOrderPayInfo($request);
            if (empty($order_info)) {
                return 0;
            }else{
                return $order_info['order_status'];
            }
        } catch (\Exception $e) { //异常回滚
            return 0;
        }             
    }

    /**
     * 后台待发货 添加物流信息
     * @author lvqing@kuaigang.net
     * @param  string
     */
    public function addLogistics(Request $request){
        try {
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->addLogistics($request);               
            if (empty($order_info)) {
                return 0;
            }else{
                return 1;
            }
        } catch (\Exception $e) { //异常回滚
            return 0;
        } 
    }

    /**
     * 后台待确认 修改订单商品信息
     * @author lvqing@kuaigang.net
     * @param  string
     */
    public function updateOrderGoodsInfo(Request $request){
        try {
            $orderGoodsService = new OrderGoodsService();
            $order_info = $orderGoodsService->updateOrderGoodsInfo($request);               
            if (empty($order_info)) {
                return 0;
            }else{
                return 1;
            }
        } catch (\Exception $e) { //异常回滚
            return 0;
        }             
    }

    /**
     * 后台设定休市时间
     * @author lvqing@kuaigang.net
     * @param  string
     */
    public function marketStatus(Request $request){
      try {
        $AdminConfigService = new AdminConfigService;
        $admin_config_info = $AdminConfigService->marketStatus($request);
        if (empty($admin_config_info)) {
            return 0;
        }else{
            return 1;
        } 
      } catch (\Exception $e) { //异常回滚
          return 0;
      }        
    }

    /**
     * 后台设定获得休市时间
     * @author lvqing@kuaigang.net
     * @param  string
     */
    public function getMarketInfo(Request $request){
      try {
        $AdminConfigService = new AdminConfigService;
        $admin_config_info = $AdminConfigService->getMarketInfo($request);
        if (empty($admin_config_info)) {
            return 0;
        }else{
            return $admin_config_info;
        } 
      } catch (\Exception $e) { //异常回滚
          return 0;
      }        
    }

    /**
     * 市场 开关
     * @author lvqing@kuaigang.net
     * @param  string
     */
    public function marketSwitch(Request $request){
      try {
        $AdminConfigService = new AdminConfigService;
        $admin_config_info = $AdminConfigService->marketSwitch($request);
        if (empty($admin_config_info)) {
            return 0;
        }else{
            return 1;
        } 
      } catch (\Exception $e) { //异常回滚
          return 0;
      }        
    }    
}
