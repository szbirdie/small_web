<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class UploadExcel extends AbstractTool
{
    protected function script()
    {
        // $url = Request::fullUrlWithQuery(['gender' => '_gender_']);
        $url = 'api/cache_flush';
        return <<<EOT

$('input:radio.user-gender').change(function () {
    alert(123);
    var url = "$url";
    $.ajax({
        method: 'get',
        url: url,
        success: function (data) {
            if (data['code'] == 200) {
                alert("所有缓存已清除");
            }else{
                alert("缓存清除失败");
            }
        }
    });

});


EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = [
            'all'   => '导入修改商品'
        ];

        return view('admin.tools.excel', compact('options'));
    }
}