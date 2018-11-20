<?php
namespace App\Services;

use App\Models\Recommend;
use App\Models\UserLevel;
use App\Models\UserLevelGoods;
use App\Models\IndexData;
use Illuminate\Http\Request;
use App\Services\CacheService;
use App\Services\OrderGoodsService;
use App\Services\GoodsService;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
use App\Models\User;

/**
 * 推荐service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class RecommendService
{
    public $recommend;
    public $order_goods;
    public $goods;
    public $user_level_goods;
    public $users;
    public $index_data;

    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {
        $this->recommend = new Recommend;
        $this->order_goods = new OrderGoodsService();
        $this->goods = new GoodsService();
        $this->user_level_goods = new UserLevelGoods();
        $this->users = new User();
        $this->index_data = new IndexData();
    }

    /**
     * 获取推荐列表数据
     * @param $position,$type,$num
     * @author zhao
     * @return fixed
     */

    public function getList($user_id,$company_id,$position,$type,$num)
    {
        $userinfo = $this->users::select('user_level_id')->where('id',$user_id)->first();
        $level_id = $userinfo->user_level_id;
        if(!$type && is_array($num)){
        //            $res = CacheService::getCache('recommend');
            $res = 0;
            if($res){
                return $res;
            }else {
                $data['banner'] = $this->recommend::where('position', 'banner')->orderBy('order', 'desc')->limit($num['banner_num'])->get()->toArray();
                $data['article'] = $this->recommend::where('position', 'article')->orderBy('order', 'desc')->limit($num['article_num'])->get()->toArray();
                $data['mould'] = $this->recommend::where('position', 'mould')->orderBy('order', 'desc')->limit($num['mould_num'])->get()->toArray();
                try{
                    $logGoods = $this->order_goods->getLogGoods($company_id,5);//已购买过的商品
                }catch(\Exception $e){
                    $logGoods = [];
                }
                if($logGoods){
                    foreach($logGoods as $lgood) {
                        $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>$lgood];
                    }
                    if(count($logGoods) < 5){
                        $n = bcsub(5,count($logGoods));
                        $res = $this->recommend::where('position', 'good')->whereNotIn('params',$logGoods)->where('type', 3)->where('state',1)->orderBy('order', 'desc')->limit($n)->get()->toArray();
                        if($res){
                            foreach($res as $k => $item){
                                $i = json_decode($item['params'],true);
                                $u_l_g = $this->user_level_goods
                                    ->select('price','goods_weight','goods_lock_weight')
                                    ->where('goods_id',$i['id'])
                                    ->where('user_level_id',$level_id)->first();
                                $i['price'] = $u_l_g->price;
                                $i['inventory'] = bcsub($u_l_g->goods_weight,$u_l_g->goods_lock_weight);
                                $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>json_encode($item)];
                            }
                            if(bcadd(count($logGoods),count($res)) < 5){
                                $num = bcsub(5,bcadd(count($logGoods),count($res)));
                                foreach($logGoods as $lGood){
                                    $ids[] = json_decode($lGood,true)['id'];
                                }
                                foreach($logGoods as $res){
                                    $ids[] = json_decode($res,true)['id'];
                                }
                                $goo = DB::table('goods')->select('goods.*','price','goods_weight','goods_lock_weight')
                                    ->leftJoin('user_level_goods as u_l_o', function ($join) {
                                        $join->on('goods.id', '=', 'u_l_o.goods_id');
                                    })->where('user_level_id',$level_id)->whereNotIn('goods.id',$ids)->limit($num)->get();
                                foreach ($goo as $k => $g){
                                    $g->inventory = bcsub($g->goods_weight,$g->goods_lock_weight);
                                    $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>json_encode($g)];
                                }
                            }
                        }else{
                            $num = bcsub(5,count($logGoods));
                            foreach($logGoods as $lGood){
                                $ids[] = json_decode($lGood,true)['id'];
                            }
                            $goo = DB::table('goods')->select('goods.*','price','goods_weight','goods_lock_weight')
                                ->leftJoin('user_level_goods as u_l_o', function ($join) {
                                    $join->on('goods.id', '=', 'u_l_o.goods_id');
                                })->where('user_level_id',$level_id)->whereNotIn('goods.id',$ids)->limit($num)->get();
                            foreach ($goo as $k => $g){
                                $g->inventory = bcsub($g->goods_weight,$g->goods_lock_weight);
                                $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>json_encode($g)];
                            }
                        }
                    }
                }else{
                    $array = $this->recommend::where('position', 'good')->where('type', 3)->orderBy('id', 'desc')->limit($num['good_num'])->get()->toArray();
                    if(count($array) < 5){
                        foreach($array as $lGood){
                            $ids[] = json_decode($lGood['params'],true)['id'];
                            $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>$lGood['params']];
                        }
                        $num = bcsub(5,count($array));
                        $goo = DB::table('goods')->select('goods.*','price','goods_weight','goods_lock_weight')
                            ->leftJoin('user_level_goods as u_l_o', function ($join) {
                                $join->on('goods.id', '=', 'u_l_o.goods_id');
                            })->where('user_level_id',$level_id)->whereNotIn('goods.id',$ids)->limit($num)->get();
                        foreach ($goo as $k => $g){
                            $g->inventory = bcsub($g->goods_weight,$g->goods_lock_weight);
                            $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>json_encode($g)];
                        }
                    }else{
                        foreach($array as $k => $item){
                            $res = DB::table('goods as g')
                                ->leftJoin('user_level_goods as u_l_o', function ($join) {
                                    $join->on('g.id', '=', 'u_l_o.goods_id');
                                })
                                ->where('g.id', $item['id'])
                                ->select('g.*', 'u_l_o.*')->get()->toArray();
                            $array[$k]['params'] = json_encode($res[0]);
                        }
                        $data['good'] = $array;
                    }
                }
                CacheService::setCache('recommend', $data,10);
                return $data;
            }
        }else{
            $res = CacheService::getCache('recommend'.$type);
            if($res){
                return $res;
            }else{
                $data = $this->recommend::select('id','name','position','type','thumb','url','params')->where('position',$position)->where('type',$type)->orderBy('id', 'desc')->limit($num)->get()->toArray();
                if(count($data) < 5){
                    $num = bcsub(5,count($data));
                    foreach($data as $lGood){
                        $ids[] = json_decode($lGood['params'],true)['id'];
        //                        $data['good'][] = json_decode($lGood['params'],true);
                        $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>$lGood['params']];
                    }
                    $goo = DB::table('goods')->select('goods.*','price','goods_weight','goods_lock_weight')
                        ->leftJoin('user_level_goods as u_l_o', function ($join) {
                            $join->on('goods.id', '=', 'u_l_o.goods_id');
                        })->where('user_level_id',$level_id)->whereNotIn('goods.id',$ids)->limit($num)->get();
                    foreach ($goo as $k => $g){
                        $g->inventory = bcsub($g->goods_weight,$g->goods_lock_weight);
                        $data['good'][] = ['id'=>0,'name'=>'','position'=>'good','type'=>3,'thumb'=>'','url'=>'','params'=>json_encode($g)];
                    }
                }else{
                    foreach($data as $k => $item){
                        $params = json_decode($item['params']);
                        $res = DB::table('goods as g')
                            ->leftJoin('user_level_goods as u_l_o', function ($join) {
                                $join->on('g.id', '=', 'u_l_o.goods_id');
                            })
                            ->where('g.id', $params->id)
                            ->select('g.*', 'u_l_o.*')->get()->toArray();
                        $array[$k]['params'] = json_encode($res[0]);
                    }
                    $data['good'] = $array;
                }
                CacheService::setCache('recommend'.$type,$data,10);
                return $data;
            }
        }
    }

    public function getRecommendBannerAndMouldList(){
        $res = array();
        $res['banner'] = $this->recommend->where(['position'=>'banner'])->get()->toArray();
        $res['mould'] = $this->recommend->where(['position'=>'mould'])->orderBy('order','desc')->get()->toArray();
        return $res;
    }

    /**
     * 获取指数信息
     */
    public function getIndexDataList(){
        $res = array();
        $res['yougang'] = $this->index_data->where(['indiceType'=>''])->where('indiceArea','00001')->orderby('steelDate','desc')->limit(1)->get()->toArray();
        $res['texian'] = $this->index_data->where(['indiceType'=>'1'])->orderby('steelDate','desc')->limit(1)->get()->toArray();
        return $res;
    }

}

