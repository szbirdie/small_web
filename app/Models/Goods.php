<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Goods extends Model
{
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            // 从$model取出数据并进行处理
            $model->labels = $model->labels ?? ' ';
            if(strchr($_SERVER['HTTP_REFERER'],'create') || strchr($_SERVER['HTTP_REFERER'],'edit')){
                $model->cost = bcmul($model->cost,100);
                $model->weight = bcmul($model->weight,1000);
            }
//            $ginfo = DB::table('goods')->where('id',$model->id)->get()->toArray();
            $array = [];
//            $array['params'] = json_encode($ginfo);
            $array['params'] = json_encode($model->toArray());

            if($model->state == 2){
                $model->recommend == 2;
            }else{
                if($model->recommend == 2){
                    $array['state'] = 1;
                }else{
                    $array['state'] = 2;
                }
                if($model->recommend_id){
                    $result = DB::table('recommend')->where('id',$model->recommend_id)->first();
                    if($result){
                        $res = DB::table('recommend')->where('id',$model->recommend_id)->update($array);
                    }else{
                        $array['name'] = 'goods'.$model->id;
                        $array['position_id'] = 3;
                        $array['position'] = 'good';
                        $array['type'] = 3;
                        $array['thumb'] = '';
                        $array['url'] = '';
                        $model->recommend_id = Recommend::insertGetId($array);
                    }
                }

            }
        });
    }

    public function userLevelGoods()
    {
        return $this->hasOne(UserLevelGoods::class);

    }
    public function order()
    {
        return $this->belongsToMany(Order::class,'order_goods','order_id','goods_id');
    }
//    public function Recommend()
//    {
//        return $this->belongsTo(Recommend::class);
//
//    }
    //
    /**
     * 商品表
     *
     * @var string
     */
    protected $table = 'goods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id",
        "name",
        "cost",
        "weight",
        "type",
        "specs_id",
        "specs_name",
        "materials_id",
        "materials_name",
        "labels_id",
        "labels",
        "factorys_id",
        "factorys_name",
        "storehouses_id",
        "storehouses_name",
        "big_categorys_id",
        "big_categorys_name",
        "recommend",
        "recommend_id",
        "distrib_level",
        "creator_id",
        "small_categorys_id",
        "small_categorys_name",
        "created_at",
        "updated_at",
        "state"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
