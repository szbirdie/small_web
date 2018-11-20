<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/14
 * Time: 16:01
 */

namespace App\Services;

use App\Models\UserLevelGoods;
use Illuminate\Http\Request;


class UserLevelGoodsService
{
    public $userLevelGoods;

    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct(UserLevelGoods $userLevelGoods)
    {
        $this->userLevelGoods = $userLevelGoods;
    }

    /**
     * 软删除
     * @param int $id
     * @return \Eloquent
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function del(int $id){
        $data = $this->userLevelGoods->where('id',$id)->first();
        $data->price = 0;
        $data->goods_weight = 0;
        $data->state = 2;
        return $data->save();
    }
}