<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

/**
 * 清除缓存操作
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class ClearCache
{

    protected $id;
    protected $resource;
    
    /**
     * 构造函数
     * @param int $id
     * @param string $resource
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function __construct(int $id,String $resource)
    {
        $this->id = $id;
        $this->resource = $resource;
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
        
$('.clear-cache').on('click', function () {

    // Your code.
    //console.log($(this).data('id'));

    var id = $(this).data('id');

    $.ajax({
        method: 'get',
        url: 'api/clear_cache/' + id,
        success: function (data) {
            if (data['code'] == 200) {
                swal('缓存已清除', '', 'success');
            }else{
                swal('缓存清除失败', '', 'success');
            }
        }
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
        
        return "<a class='btn btn-xs btn-success fa  clear-cache' data-id='{$this->id}'>清除</a>";
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
    
    /**
     * 返回resource
     * @return String
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function getResource(){
        return $this->resource;
    }
    
    
}

