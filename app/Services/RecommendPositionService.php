<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/13
 * Time: 11:14
 */

namespace App\Services;

use App\Models\RecommendPosition;

class RecommendPositionService
{
    public $recommend_position;

    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {
        $this->recommend_position = new RecommendPosition;
    }

    public function getNum($position,$type){
        if(!$type){
            $res1 = $this->recommend_position::where('name','banner')->first();
            $num['banner_num'] = $res1->num;
            $res2 = $this->recommend_position::where('name','article')->first();
            $num['article_num'] = $res2->num;
            $res3 = $this->recommend_position::where('name','good')->first();
            $num['good_num'] = $res3->num;
            $res4 = $this->recommend_position::where('name','mould')->first();
            $num['mould_num'] = $res4->num;
        }else{
            $res = $this->recommend_position::where('name',$position)->where('type',$type)->first();
            $num = $res->num;
        }
        if($num){
            return $num;
        }else{
            return 0;
        }
    }

}