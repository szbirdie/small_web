<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/28
 * Time: 15:35
 */

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class Distribute
{
    protected $id;
    protected $resource;

    /**
     * 构造函数
     * @param int $id
     * @param string $resource
     */
    public function __construct(int $id,String $resource)
    {
        $this->id = $id;
        $this->resource = $resource;
    }

    /**
     * 点击这个按钮执行的js
     * @return string
     */
    protected function script()
    {
        return <<<SCRIPT
        
$('.grid-distribute').on('click', function () {

    var str = window.location.href;
    var position1 = str.indexOf("is_distribute=");
    var position2 = str.indexOf("user_level_id=");
    if(str.substring(position1+14,position1+15) == 0){
        var type = 'insert';
    }else{
        var type = 'update';
    }
    var level_id = document.getElementById("level").value;
    
    var id = $(this).data('id');
    var price = document.getElementById("level_price"+id).value;
    var weight = document.getElementById("goods_weight"+id).value;
    var gid = document.getElementById("goods_id"+id).innerHTML;
    
    swal({
      title: "确认分配！",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "确认",
      closeOnConfirm: false,
      cancelButtonText: "取消"
    },
    function(){
        $.ajax({
            type: 'post',
            url: '{$this->getResource()}/' + id+'/distribute',
            data: {
                _token:LA.token,
                id:id,
                price:price,
                weight:weight,
                gid:gid,
                type:type,
                level_id:level_id,
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');
                if (typeof data === 'object') {
                    if (data.status) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
            }
        });
    });
    
});

SCRIPT;
    }


    /**
     * 生成toString 内容
     * @return string
     */
    protected function render()
    {
        Admin::script($this->script());
        return "<a class='btn btn-xs btn-success fa  grid-distribute' data-id='{$this->id}'>分配</a>";
    }

    /**
     * 重写这个类的toString 方法
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * 返回resource
     * @return String
     */
    public function getResource(){
        return $this->resource;
    }

}