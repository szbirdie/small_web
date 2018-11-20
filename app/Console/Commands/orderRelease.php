<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\UserLevelGoods;
use App\Models\AdminConfig;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
/**
 * 订单定时释放
 */
class orderRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'order release';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = time()-7200;
        //获取2个小时外的订单数据
        $order_list = Order::select('orders.id', 'users.user_level_id')
            ->where(['orders.order_status'=>1])
            ->where('orders.created_at','<',date("Y-m-d H:i:s",$time))
            ->join('users','orders.user_id','=','users.id')
            ->get()
            ->toArray();
        try {
            //更新商品等级 释放库存  2、订单和商品订单修改 状态
            foreach ($order_list as $key => $value) {
                Log::info('order_id_list_-1',['order_id'=>$value['id']]);
                $OrderGoodsList = OrderGoods::select('goods_id', 'weight')->where(['order_id'=>$value['id']])->get()->toArray();
                foreach ($OrderGoodsList as $k => $val) {
                    $res = UserLevelGoods::where(['user_level_id'=>$value['user_level_id'], 'goods_id'=>$val['goods_id']])->update(array(
                       'goods_lock_weight' => \DB::raw('goods_lock_weight -'.$val['weight'])
                    ));
                }
                Order::where(['id'=>$value['id']])->update(['order_status'=>'-1']);
                OrderGoods::where(['order_id'=>$value['id']])->update(['order_status'=>'-1']);                
            }
        } catch (\Exception $e) {
            Log::info('order_id_list_-1',['message'=>'自动取消库存失败', 'order_list'=>$order_list]);
        }
    }
}
