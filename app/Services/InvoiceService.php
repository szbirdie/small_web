<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/12
 * Time: 13:22
 */

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public $invoice;

    /**
     * 注入Invoice 对象实例
     */
    public function __construct()
    {
        $this->invoice = new Invoice;
        $this->order = new Order;
    }

    /**
     * 获取发票列表数据
     * @param $request
     * @author zhao
     * @return fixed
     */
    public function getList(Request $request,$company_id)
    {
        $cid = $company_id;
        $status = $request->get('status');


        $num = $request->num;
        if(!$num){
            $num = 10;
        }

        $select = array('orders.*','invoice.status as invoice_status');

        if($status===null){

            $data = $this->invoice::where('invoice.company_id',$cid)
                ->join('orders', 'orders.id','=', 'invoice.order_id')
                ->orderBy('invoice.id', 'desc')
                ->select($select)
                ->paginate($num);
        }else{

            $data = $this->invoice::where('invoice.company_id',$cid)
                ->join('orders', 'orders.id','=', 'invoice.order_id')
                ->where('invoice.status',$status)
                ->select($select)
                ->orderBy('invoice.id', 'desc')
                ->paginate($num);


        }




        $data = json_decode(json_encode($data),true);
        $order_goods_data = $data['data'];

        $new=array();
        $i=0;
        foreach ($order_goods_data as $v){

            $new[$i]=$v;
            $new[$i]['order_goods'] = DB::table('order_goods')->where('order_id',$v['id'])->orderBy('id', 'ASC')->get();


            $i++;

        }

        return $new;


    }

    /**
     * 获取发票详细数据
     * @param $request
     * @author zhao
     * @return fixed
     */
    public function getDetails(Request $request,$company_id)
    {
        $cid = $company_id;
        $oid = $request->get('order_id');
        $iid = $request->get('invoice_id');
        $data = $this->invoice::where('order_id',$oid)->where('company_id',$cid)->where('id',$iid)->first();
        if($data){
            return $data->toArray();
        }else{
            return $data;
        }

    }

    /**
     * 发票申请
     * @author zhao
     * @param $data
     * @return fixed
     */
    public function invoice_apply($data)
    {
        $res = $this->invoice::where('user_id',$data['user_id'])->where('company_id',$data['company_id'])->where('goods_id',$data['goods_id'])->where('order_id',$data['order_id'])->first();
        if(!$res){
            DB::beginTransaction();
            $res = $this->invoice::insertGetId($data);
            $res1 = $this->order::where('id',$data['order_id'])->update(array('invoice_status'=>3));
            if($res && $res1){
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        }else {
            return '已申请';
        }
    }

}