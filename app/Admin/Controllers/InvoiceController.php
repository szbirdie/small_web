<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/8
 * Time: 16:15
 */

namespace App\Admin\Controllers;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Models\Invoice;
use App\Models\Order;
use Encore\Admin\Controllers\HasResourceActions;
use App\Admin\Extensions\DrawInvoice;
use App\Admin\Extensions\CancelInvoice;
use App\Admin\Extensions\ExcelExpoter;

class InvoiceController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     * 搜索及列表页面
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('发票管理')
            ->description(trans('发票管理'))
            ->body($this->grid()->render());
    }

    /**
     * Show interface.
     * 详情页面
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('查看')
            ->description('查看')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     * 编辑页面
     * @param $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     * 添加页面
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建')
            ->description('创建')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     * 列表方法
     * @return Grid
     */
    protected function grid()
    {
        $grid = Admin::grid(Invoice::class, function (Grid $grid) {
            $grid->model()->orderby('updated_at','desc');
            $grid->user_name('用户昵称')->sortable();
            $grid->company_name('所属公司')->sortable();
            $grid->order_no('相关订单号')->sortable();
            $grid->order_price('开票金额')->display(function ($order_price) {
                return bcdiv($order_price,100,2);
            });
            $grid->created_at('申请时间')->sortable();
            $grid->status('状态')->display(function ($status) {
                if($status == 1){
                    return '<font color=\'green\'>已开票</font>';
                }elseif($status == 0){
                    return '<font color=\'red\'>待开票</font>';
                }elseif($status == 3){
                    return '<font color=\'black\'>取消开票</font>';
                }
            });

            $export_arr = Invoice::where('status','!=',1)->get()->toArray();
            $excel = new ExcelExpoter();
            $excel->setAttr(
                ['用户昵称','订单号', '商品名称', '商品重量','商品单价','开票金额','公司名称','收货人','手机号','固话',
                    '省','市','区','详细地址','税务登记号','基本户开户账户','基本户开户账户','基本户开户名','公司注册地址','公司联系方式','状态'],
                ['user_name','order_no', 'goods_name', 'weight','goods_price','order_price','company_name',
                    'consignee_name','consignee_phone','consignee_landline_phone','province','city','area','consignee_address',
                    'tax_registration_number','accounts_bank','deposit_account','bank_account_name','company_address','company_tel','state'],
                $export_arr,
                '开票信息','开票信息');
            $grid->exporter($excel);

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                // 在这里添加字段过滤器
                $filter->like('order_no', '订单号');
                $filter->like('user_name', '用户昵称');
                $filter->like('company_name', '所属公司');
                $filter->equal('status', '开票状态')->select(array(" "=>"全部","0"=>"待开票","1"=>"已开票"));
                $filter->between('created_at', '申请时间')->datetime();
            });
            $grid->disableCreation();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
//                $actions->disableView();
//                 Log::info('actions',['actions'=> $actions]);
                // prepend一个操作
                $invoice = Invoice::select('status')->where('id',$actions->getKey())->first();
                if(!$invoice->status){
                    $actions->append(new DrawInvoice($actions->getKey(), $actions->getResource()));
                    $actions->append(new CancelInvoice($actions->getKey(), $actions->getResource()));
                }
            });

        });

        return $grid;
    }

    /**
     * Make a show builder.
     * 查看详情方法
     * @param mixed $id
     *
     * @return Show
     * @version 1.0.0
     * @author zhao
     */
    protected function detail($id)
    {
        $show = new Show(Invoice::findOrFail($id));
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->user_name('用户昵称');
        $show->company_name('所属公司');
        $show->order_no('相关订单号');
        $show->order_price('开票金额');
        $show->created_at('申请时间');
        $show->status('状态')->as(function ($status) {
            if($status == 1){
                return '<font color=\'green\'>已开票</font>';
            }elseif($status == 0){
                return '<font color=\'red\'>待开票</font>';
            }
        });

        $show->tax_registration_number('税务登记号');
        $show->accounts_bank('基本户开户银行');
        $show->deposit_account('基本户开户账户');
        $show->bank_account_name('基本户开户名');
        $show->company_address('公司注册地址');
        $show->company_tel('公司联系方式');

        $show->consignee_name('收件人');
        $show->consignee_phone('收件人手机号');
        $show->consignee_landline_phone('收件人固话');
        $show->consignee_address('收件地址');

        return $show;
    }

    /**
     * 状态变更
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function change(int $id){
        $res = Invoice::where('id',$id)->update(array('status'=>1));
        if($res){
            $invoice_info = Invoice::find($id);
            Order::where('id',$invoice_info['order_id'])->update(array('invoice_status'=>2));
            return response()->json([
                'status'  => true,
                'message' => '变更成功 !',
            ]);
        }else{
            return response()->json([
                'status'  => false,
                'message' => '变更失败 !',
            ]);
        }
    }

    /**
     * 取消订单
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(int $id){
        $res = Invoice::where('id',$id)->update(array('status'=>3));
        if($res){
            $invoice_info = Invoice::find($id);
            Order::where('id',$invoice_info['order_id'])->update(array('invoice_status'=>4));
            return response()->json([
                'status'  => true,
                'message' => '变更成功 !',
            ]);
        }else{
            return response()->json([
                'status'  => false,
                'message' => '变更失败 !',
            ]);
        }
    }

}