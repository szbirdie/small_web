<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;

/**
 * 选取订单商品
 * @author lvqing
 * @version 1.0.0
 */
class GoodsChoose
{

    protected $id;
    
    protected $weight;//可购买的重量
    protected $cost;//成本价

    /**
     * 构造函数
     * @param int $id
     * @version 1.0.0
     * @author lvqing
     */
    public function __construct(int $id, $array)
    {
        $this->id = $id;
        $weight = $array->user_level_goods['goods_weight'] - $array->user_level_goods['goods_lock_weight'];
        $this->weight = $weight/1000;
        $this->cost = $array->user_level_goods['cost']/100;
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
    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }        
    $('.grid-reset-pass').on('click', function () {
        // Your code.
        //console.log($(this).data('id'));
        var id = $(this).data('id');
        var weight_num = $(this).data('weight');
        var cost_num = $(this).data('cost');        
        var order_status = GetQueryString("order_status");
        var goods_id = GetQueryString("goods_id");
        var user_level_id = GetQueryString("user_level_id");

        // alert(weight_num);
        $('fieldset > input:eq(0)').css('display','none');
        swal({
            title: "<small>该等级可以购买数量：" + weight_num  + "吨</small>",
          text: "单价(成本价为" + cost_num + "元) <input type='text' name='price' id='jfdw'>"
          +"购买数量 <input type='text' name='weight' id='jfdw'>",
          html: true,
          type: "prompt",
          //animation: "slide-from-top",
          showCancelButton: true,
          cancelButtonText: "取消",
          closeOnConfirm: false
        },
        function(isConfirm){
            var reg = /^[0-9]+(.[0-9]{1,2})?$/;

            if (isConfirm != false) {
                if ($("input[name=price]").val() == '' || !reg.test($("input[name=price]").val())) {
                    alert("请输入正确价格");
                    return false;
                }
                if ($("input[name=weight]").val() == '' || !reg.test($("input[name=weight]").val())) {
                    alert("请输入正确吨数");
                    return false;
                }
                // if($("input[name=price]").val() < cost_num){
                //     alert("不得低于成本价");
                //     return false;                  
                // }
                if($("input[name=weight]").val() > weight_num){
                    alert("不得高于可购买库存");
                    return false;                  
                }                            
                $.ajax({
                    method: 'get',
                    url: 'api/update_order_goods',
                    data: {
                        goods_id:id,
                        old_goods_id:goods_id,
                        weight:$("input[name=weight]").val(),
                        price:$("input[name=price]").val(),
                        order_id:GetQueryString("order_id"),
                        order_goods_id:GetQueryString("order_goods_id")
                    },
                    success: function (data) {
                        console.log(data);
                        if (data == 1) {
                            window.location.href="order_list?order_status=" + order_status ;
                        }else{
                            swal('修改错误', '', 'error');
                        } 
                    }
                });            
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
        
        return "<a class='btn btn-xs btn-success fa  grid-reset-pass' data-id='{$this->id}' data-weight='{$this->weight}' data-cost='{$this->cost}'>选取</a>";
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

