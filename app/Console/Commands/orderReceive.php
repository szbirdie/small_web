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
 * 订单定时收货
 */
class orderReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'order receive';

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
        $time = time()-691200;
        //获取7个小时外的订单数据
        $order_list = Order::select('orders.id', 'users.user_level_id')
            ->where(['order_status'=>4])
            ->where('created_at','<',date("Y-m-d H:i:s",$time))
            ->join('users','orders.user_id','=','users.id')
            ->get()
            ->toArray();
        try {
            //更新商品等级 释放库存  2、订单和商品订单修改 状态
            foreach ($order_list as $key => $value) {
                Log::info('order_id_list_5',['order_id'=>$value['id']]);
                $OrderGoodsList = OrderGoods::select('goods_id', 'weight')->where(['order_id'=>$value['id']])->get()->toArray();
                foreach ($OrderGoodsList as $k => $val) {
                    $res = UserLevelGoods::where(['user_level_id'=>$value['user_level_id'], 'goods_id'=>$val['goods_id']])->update(array(
                        'goods_lock_weight' => \DB::raw('goods_lock_weight -'.$val['weight']),
                        'goods_weight' => \DB::raw('goods_weight -'.$val['weight'])
                    ));
                }
                Order::where(['id'=>$value['id']])->update(['order_status'=>5, 'done_type'=>2]);
                OrderGoods::where(['order_id'=>$value['id']])->update(['order_status'=>5]);
                
            }
        } catch (\Exception $e) {
            Log::info('order_id_list_-1',['message'=>'自动收货失败', 'order_list'=>$order_list]);
        }

    }
}
