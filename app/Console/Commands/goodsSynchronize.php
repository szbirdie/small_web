<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AdminConfig;
use GuzzleHttp\Client;

/**
 * 商品同步ERP自动处理
 */
class goodsSynchronize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goods:synchronize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'goods synchronize';

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
        $md5 = md5('TBWZDMJK');
        $time = time()-86400;
        //获取2个小时外的订单数据
        $order_list = DB::table('goods')->select('id', 'specs_name', 'materials_name', 'factorys_name', 'big_categorys_name', 'small_categorys_name')
            ->where('state','!=',2)
            ->whereBetween('created_at', [date("Y-m-d H:i:s",$time), date("Y-m-d H:i:s",time())])
            ->get()
            ->toArray();
        $id = array();
        $json_key = '';
        foreach ($order_list as $key => $value) {
            $id[] = $value->id;
            unset($value->id);
            //设定第一个参数作为key值
            // if ($key == 0) {
                $materials_name_md5 = md5($value->materials_name);
                $value->key = strtoupper($materials_name_md5.$md5);
            // }
            $value->partsnameName = $value->small_categorys_name;
            unset($value->small_categorys_name);
            $value->goodsMaterial = $value->materials_name;
            unset($value->materials_name);
            $value->goodsSpec = $value->specs_name;
            unset($value->specs_name);
            $value->productareaName = $value->factorys_name;
            unset($value->factorys_name);
            $value->pntreeName = $value->big_categorys_name;
            unset($value->big_categorys_name);
        }
        $http = new Client(['headers' => ['content-type'=> 'application/x-www-form-urlencoded;charset=utf-8']]);
        $response = $http->request('POST', env('ERP_URL').'kuaigangAjax!updateGoodsCode.do',[
            'form_params' =>$order_list
        ]);
        $data = json_decode((string) $response->getBody(), true);
        $result = $data[0];
        if (!empty($result)) {
            Log::info('goodsSynchronize',['failed'=>"定时商品同步ERP！",'message'=>$result['message'], 'id'=>$id]);
        }
    }
}
