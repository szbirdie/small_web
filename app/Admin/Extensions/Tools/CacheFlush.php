<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class CacheFlush extends AbstractTool
{
    protected function script()
    {
        // $url = Request::fullUrlWithQuery(['gender' => '_gender_']);
        $url = 'api/cache_flush';
        return <<<EOT

$('input:radio.user-gender').change(function () {

    var url = "$url";
    $.ajax({
        method: 'get',
        url: url,
        success: function (data) {
            if (data['code'] == 200) {
                swal('所有缓存已清除', '', 'success');
            }else{
                swal('缓存清除失败', '', 'success');
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
            'all'   => '清除所有缓存'
        ];

        return view('admin.tools.cache', compact('options'));
    }
}