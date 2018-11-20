<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

/**
 * 自定义重置密码扩展
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class ResetPass
{

    protected $id;
    
    /**
     * 构造函数
     * @param int $id
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }
    
    /**
     * 点击这个按钮执行的js
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    protected function script()
    {
        return <<<SCRIPT
        
$('.grid-reset-pass').on('click', function () {

    // Your code.
    //console.log($(this).data('id'));

    var id = $(this).data('id');

    swal({
      title: "确认重置密码",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "确认重置密码",
      closeOnConfirm: false,
      cancelButtonText: "取消"
    },
    function(){
        $.ajax({
            method: 'post',
            url: 'users/'+id+'/resetPass',
            data: {
                _method:'post',
                _token:LA.token,
            },
            success: function (data) {
//                 $.pjax.reload('#pjax-container');
                //console.log(data);
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
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    protected function render()
    {
        Admin::script($this->script());
        
        return "<a class='btn btn-xs btn-success fa  grid-reset-pass' data-id='{$this->id}'>重置密码</a>";
    }
    
    /**
     * 重写这个类的toString 方法
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function __toString()
    {
        return $this->render();
    }
}

