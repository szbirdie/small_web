<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/6
 * Time: 14:36
 */

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class LockWeightChange
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
        
$('.grid-lock-weight').on('click', function () {

    var str = window.location.href;
    var level_id = document.getElementById("level").value;
    var id = $(this).data('id');
    var gid = document.getElementById("goods_id"+id).innerHTML;
    var goods_lock_weight = document.getElementById("goods_lock_weight"+id).value;

    swal({
      title: "确认修改已锁定库存数量！",
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
            url: '{$this->getResource()}/' + id+'/lock_weight_change',
            data: {
                _token:LA.token,
                id:id,
                gid:gid,
                goods_lock_weight:goods_lock_weight,
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
        return "<a class='btn btn-xs btn-success fa  grid-lock-weight' data-id='{$this->id}'>改锁</a>";
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