<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/25
 * Time: 17:28
 */

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CancelInvoice
{
    protected $id;
    protected $resource;

    /**
     * 构造函数
     * @param int $id
     * @param int $status
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
        
$('.grid-cancel-state').on('click', function () {

    var id = $(this).data('id');

    swal({
      title: "确认状态变更！",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "确认",
      closeOnConfirm: false,
      cancelButtonText: "取消"
    },
    function(){
        $.ajax({
            method: 'post',
            url: '{$this->getResource()}/' + id+'/cancel',
            data: {
                _method:'delete',
                _token:LA.token,
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
        return "<a class='btn btn-xs btn-success fa  grid-cancel-state' data-id='{$this->id}'>取消开票</a>";
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