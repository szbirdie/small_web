<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    /**
     * 一对一关联公司用户绑定
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->belongsTo(User::class);
        
    }
    /**
     * 一对一关联与订单商品关联
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */    
    public function orderGoods()
    {
        return $this->hasOne(OrderGoods::class);
    } 

    public function goods()
    {
        /*
         * 第一个参数：要关联的表对应的类
         * 第二个参数：中间表的表名
         * 第三个参数：当前表跟中间表对应的外键
         * 第四个参数：要关联的表跟中间表对应的外键
         * */
        return $this->belongsToMany(Goods::class,'order_goods','order_id','goods_id');

    }
    //
    /**
     * 父级定单表
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id",
        "user_id",
        "company_id",
        "order_status",
        "order_no",
        "total_price",
        "total_weight",
        "order_goods",
        "order_marks",
        "order_src",
        "cancel_reason",
        "consignee_name",
        "consignee_address",
        "consignee_phone",
        "is_pay",
        "last_update_user",
        "area_id",
        "area_name_path",
        "pay_price",
        "invoice_status",
        "contracts_status",
        "certificate_img",
        "done_type",
        'order_confirm',
        "created_at"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "updated_at", "state"
    ];
}
