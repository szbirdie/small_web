<?php

use Encore\Admin\Facades\Admin;
use App\Admin\Extensions\WangEditor;
use App\Admin\Extensions\Map;
use Encore\Admin\Form;

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);
//引入订单JS
Admin::js('/js/order.js');
// Admin::js('/js/ajaxfileupload.js');
// Admin::js('/js/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.min.js');
Admin::css('/css/style.css');
Admin::css('/js/bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css');
//设置新的前段模板存放位置
// app('view')->prependNamespace('admin', resource_path('views/admin'));

Form::extend('editor', WangEditor::class);
Form::extend('map', Map::class);