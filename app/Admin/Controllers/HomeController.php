<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
/**
 * 后台管理默认页面
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class HomeController extends Controller
{
    
    /**
     * 默认页面显示方法 
     * @return \Encore\Admin\Layout\Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            //Environment 信息
            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append(Dashboard::environment());
                });
            });
        });
    }
}
