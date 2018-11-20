<?php

namespace App\Admin\Controllers;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Admin\Extensions\ResetPass;
use App\Admin\Extensions\SoftDelete;
use App\Models\AdminConfig;
use App\Services\WxService;

class AdminConfigController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('休市规则')
            ->body(view('admin.charts.close'));
    }

    /**
     * 生成小程序二维码页面
     *  @return Content
     */
    public function qrCode(Content $content)
    {
        return $content
            ->header('生成二维码')
            ->body(view('admin.charts.qrcode'));
    }

    /**
     *  获取二维码
     */
    public function getQrCode()
    {
        $adminId    = Admin::user()->id;
        $param      = ['path'=>"/pages/login/login?sales_id={$adminId}"];
        $qrCode     = WxService::createQrCode($param);
        header("Content-type: image/jpeg");
        echo $qrCode;
        exit;
    }


}
