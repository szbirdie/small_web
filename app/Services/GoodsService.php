<?php
namespace App\Services;

use App\Models\UserLevel;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Category;
use App\Models\Factory;
use App\Models\Storehouse;
use App\Models\Spec;
use App\Models\Material;
use App\Models\SearchesHot;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;

/**
 * 商品service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class GoodsService
{
    public $goods;
	public $category;
    public $factory;
    public $storehouse;
    public $spec;
    public $material;
    public $SearchesHots;

    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {
        $this->goods = new Goods;
        $this->category = new Category;
        $this->factory = new Factory;
        $this->storehouse = new Storehouse;
        $this->spec = new Spec;
        $this->material = new Material;
        $this->SearchesHot = new SearchesHot;

    }
    /**
     * 获取商品列表数据（关联用户等级）
     * @author lvqing@kuaigang.net
     * @param $perPage 一行显示多少条
     * @param $page 目前页数
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */
    public function getList($perPage, $page, $where = array(), $select = '*'){
		return $this->goods->from('goods as gs')
				->join('user_level_goods as le','gs.id','=','le.goods_id')
                ->join('factorys','gs.factorys_id','=','factorys.id')
				->select($select)
				->where($where)
				->orderBy('gs.recommend', 'desc') //推荐优先显示--推荐位,1为正常，2为推荐
                ->orderBy('gs.id', 'desc')
				->paginate($perPage);
    }
    public function getListNum($where = array(), $whereNotIn = array(), $num = 5, $select = '*'){
        if (empty($whereNotIn)) {
            return $this->goods
            ->select('goods.*', 'user_level_goods.price', 'user_level_goods.goods_weight', 'user_level_goods.goods_lock_weight','thumb')
            ->Join('user_level_goods','goods.id','=','user_level_goods.goods_id')
            ->leftJoin('factorys','goods.factorys_id','=','factorys.id')
            ->where($where)->orderBy('goods.recommend', 'desc')
            ->limit($num)->get()->toArray();
        }else{
            return $this->goods->where($where)
            ->select('goods.*', 'user_level_goods.price', 'user_level_goods.goods_weight', 'user_level_goods.goods_lock_weight','thumb')
            ->Join('user_level_goods','goods.id','=','user_level_goods.goods_id')
            ->leftJoin('factorys','goods.factorys_id','=','factorys.id')
            ->where($where)
            ->whereNotIn('goods.id', $whereNotIn)->orderBy('goods.recommend', 'desc')->limit($num)->get()->toArray();
        }

    }
    /**
     * 获取分类列表
     * @author lvqing@kuaigang.net
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */
    public function getCategoryList($select = '*'){
        $category_list = CacheService::getCache(config('cache.cache_name.category_list'));
        $category_list = '';
        if (empty($category_list)) {

            $category_list = $this->category->select($select)->orderBy('order', 'desc')->where('state',1)->get()->toArray();

            $category_list_json = json_encode($category_list);
            CacheService::setCache(config('cache.cache_name.category_list'),$category_list_json,10);
        }
		return $category_list;
    }

    /**
     * 商品筛选条件列表
     * @author lvqing@kuaigang.net
     * @return $request
     *
     * zhangchuang 1017 edit
     *
     *
     */
    public function goodsSearchList($search='',$small_category_parent_id=''){



        $where=array();
        $data= array();
        $cache_key='';

        if(!empty($search)){ //搜索名称查

            if(!empty($small_category_parent_id)){

                $cache_key= md5(urlencode($search));
                $data = CacheService::getCache($cache_key);
                $where[]= array('goods.name', 'like' , '%'.$search.'%');
            }

        }else{ //子分类查

            if(!empty($small_category_parent_id)){

                $cache_key= md5($small_category_parent_id);
                $data = CacheService::getCache($cache_key);
                $where[]= array('goods.small_categorys_id', '=' , $small_category_parent_id);

            }


        }

        $data=array();
        if (empty($data)) {

            $data = array();


            $data['big_category'] = $this->category->where('parent_id' , 0)->where('state' , 1)->select(['id', 'name'])->orderBy('order', 'desc')->get()->toArray();
            //仅查询子分类id
            $data['small_category'] = $this->category->where('parent_id' ,'>' , 0)->where('state' , 1)->select(['id', 'name','parent_id'])->orderBy('order', 'desc')->get()->toArray();


            $data['big_category']= $this->foreach_list($data['big_category']);
            $data['small_category']= $this->foreach_list($data['small_category']);


            $data_all = $this->goods->select('goods.*','materials.state as materials_state','specs.state as specs_state',
                'factorys.state as factorys_state','storehouses.state as storehouses_state')
                ->leftJoin('materials', 'goods.specs_id','=', 'materials.id')
                ->leftJoin('specs', 'goods.materials_id','=', 'specs.id')
                ->leftJoin('factorys', 'goods.factorys_id','=', 'factorys.id')
                ->leftJoin('storehouses', 'goods.storehouses_id','=', 'storehouses.id')
                ->where($where)
                ->get()
                ->toArray();

            $factory= array();
            $spec= array();
            $material= array();
            $storehouse= array();

            foreach ($data_all as $v){

                if($v['factorys_state']==1){

                    $factory[$v['factorys_id']]['id']=   $v['factorys_id'];
                    $factory[$v['factorys_id']]['name']= $v['factorys_name'];
                }

                if($v['storehouses_state']==1){

                    $storehouse[$v['storehouses_id']]['id']=   $v['storehouses_id'];
                    $storehouse[$v['storehouses_id']]['name']= $v['storehouses_name'];
                }

                if($v['specs_state']==1){

                    $spec[$v['specs_id']]['id']=   $v['specs_id'];
                    $spec[$v['specs_id']]['name']= $v['specs_name'];
                }

                if($v['materials_state']==1){

                    $material[$v['materials_id']]['id']=   $v['materials_id'];
                    $material[$v['materials_id']]['name']= $v['materials_name'];
                }

            }


            $data['factory']= $this->foreach_list($factory);
            $data['storehouse'] = $this->foreach_list($storehouse);
            $data['spec'] = $spec;
            $data['material'] = $material;


            if(!empty($cache_key)){

                $search_list_json = json_encode($data);
                CacheService::setCache($cache_key,$search_list_json);

            }



            return $data;

        }

        return json_decode($data,true);






    }

    /**
     * 商品筛选条件列表
     * @author lvqing@kuaigang.net
     * @return $request
     *
     * zhangchuang 1017 edit
     *
     *
     */
    public function goodsSearchList_v2($search='',$big_categorys_id='',$small_category_parent_id='',$factorys_id='',$storehouses_id='',$specs_id='',$materials_id=''){



        $where=array();
        $data= array();
        $cache_key='';

        if(!empty($search)){ //搜索名称查

            if(!empty($search)){

                $cache_key= md5(urlencode($search));
                $data = CacheService::getCache($cache_key);
                $where[]= array('goods.name', 'like' , '%'.$search.'%');
            }

        }else{ //子分类查



            $cache_key= md5($big_categorys_id.'_'.$small_category_parent_id.'_'.$factorys_id.'_'.$storehouses_id.'_'.$specs_id.'_'.$materials_id);
            $data = CacheService::getCache($cache_key);


            if(!empty($big_categorys_id)){

                $where[]= array('goods.big_categorys_id', '=' , $big_categorys_id);
            }

            if(!empty($small_category_parent_id)){

                $where[]= array('goods.small_categorys_id', '=' , $small_category_parent_id);
            }


            //$factorys_id,$storehouses_id,$specs_id,$materials_id
            if(!empty($factorys_id)){

                $where[]= array('goods.factorys_id', '=' , $factorys_id);
            }

            if(!empty($storehouses_id)){

                $where[]= array('goods.storehouses_id', '=' , $storehouses_id);
            }

            if(!empty($specs_id)){

                $where[]= array('goods.specs_id', '=' , $specs_id);
            }

            if(!empty($materials_id)){

                $where[]= array('goods.materials_id', '=' , $materials_id);

            }




        }

        $data=array();
        if (empty($data)) {

            $data = array();


            $data['big_category'] = $this->category->where('parent_id' , 0)->where('state' , 1)->select(['id', 'name'])->orderBy('order', 'desc')->get()->toArray();
            //仅查询子分类id

            if(!empty($big_categorys_id)){

                $where_category[]= array('parent_id', '=' , $big_categorys_id);
            }else{

                $where_category[]= array('parent_id' ,'>' , 0);

            }
            $data['small_category'] = $this->category->where($where_category)->where('state' , 1)->select(['id', 'name','parent_id'])->orderBy('order', 'desc')->get()->toArray();

            $data['big_category']= $this->foreach_list($data['big_category']);
            $data['small_category']= $this->foreach_list($data['small_category']);


            $data_all = $this->goods->select('goods.*','materials.state as materials_state','specs.state as specs_state',
                'factorys.state as factorys_state','storehouses.state as storehouses_state')
                ->leftJoin('materials', 'goods.specs_id','=', 'materials.id')
                ->leftJoin('specs', 'goods.materials_id','=', 'specs.id')
                ->leftJoin('factorys', 'goods.factorys_id','=', 'factorys.id')
                ->leftJoin('storehouses', 'goods.storehouses_id','=', 'storehouses.id')
                ->where($where)
                ->get()
                ->toArray();

            $factory= array();
            $spec= array();
            $material= array();
            $storehouse= array();

            foreach ($data_all as $v){

                if($v['factorys_state']==1){

                    $factory[$v['factorys_id']]['id']=   $v['factorys_id'];
                    $factory[$v['factorys_id']]['name']= $v['factorys_name'];
                }

                if($v['storehouses_state']==1){

                    $storehouse[$v['storehouses_id']]['id']=   $v['storehouses_id'];
                    $storehouse[$v['storehouses_id']]['name']= $v['storehouses_name'];
                }

                if($v['specs_state']==1){

                    $spec[$v['specs_id']]['id']=   $v['specs_id'];
                    $spec[$v['specs_id']]['name']= $v['specs_name'];
                }

                if($v['materials_state']==1){

                    $material[$v['materials_id']]['id']=   $v['materials_id'];
                    $material[$v['materials_id']]['name']= $v['materials_name'];
                }

            }


            $data['factory']= $this->foreach_list($factory);
            $data['storehouse'] = $this->foreach_list($storehouse);
            $data['spec'] = $spec;
            $data['material'] = $material;


            if(!empty($cache_key)){

                $search_list_json = json_encode($data);
                CacheService::setCache($cache_key,$search_list_json,60*24,4);

            }



            return $data;

        }

        return json_decode($data,true);






    }

    public function foreach_list($data,$type=1){

        $new_arr=array();
        $new_arr[]= array('id'=>'','name'=>'不限');

        foreach ($data as $v){

            $new_arr[]=$v;

        }

        return $new_arr;



    }



    //热门搜索 zhangchuang
    public function searches_hots(){

        $res = CacheService::getCache(config('cache.cache_name.searches_hots_list'));

        if (empty($res)) {
            $where=array(
                'state'=>1
            );
            $res=$this->SearchesHot->where($where)->get()->toArray();
            $res_json = json_encode($res);
            CacheService::setCache(config('cache.cache_name.searches_hots_list'),$res_json,10);
        }else{

            $res= json_decode($res,true);
        }



        return $res;

    }

    //购买过的商品 zhangchuang
    public function buy_goods_history($userinfo,$limit=10,$search='',$where_param=[]){
        $where=array();
        if(!empty($search)){
            $where[] = ['goods.name', 'like' , '%'.$search.'%'];
        }

        $where['orders.company_id']=$userinfo['company_id'];
        $where['orders.order_status']=5;
        $where['goods.state']=1;
        $where['user_level_goods.user_level_id']=$userinfo['user_level_id'];


        if(!empty($where_param)){

            if(isset($where_param['gs.factorys_id']) && !empty($where_param['gs.factorys_id'])){
                $where['goods.factorys_id']=$where_param['gs.factorys_id'];
            }
            if(isset($where_param['gs.storehouses_id']) && !empty($where_param['gs.storehouses_id'])){
                $where['goods.storehouses_id']=$where_param['gs.storehouses_id'];
            }
            if(isset($where_param['gs.specs_id']) && !empty($where_param['gs.specs_id'])){
                $where['goods.specs_id']=$where_param['gs.specs_id'];
            }
            if(isset($where_param['gs.materials_id']) && !empty($where_param['gs.materials_id'])){
                $where['goods.materials_id']=$where_param['gs.materials_id'];
            }
            if(isset($where_param['gs.big_categorys_id']) && !empty($where_param['gs.big_categorys_id'])){
                $where['goods.big_categorys_id']=$where_param['gs.big_categorys_id'];
            }
            if(isset($where_param['gs.small_categorys_id']) && !empty($where_param['gs.small_categorys_id'])){

                $where['goods.small_categorys_id']=$where_param['gs.small_categorys_id'];
            }

        }


        $select = array(
            'orders.id as order_id',
            'goods.id',
            'goods.small_categorys_name',
            'goods.specs_name',
            'goods.materials_name',
            'goods.type',
            'goods.factorys_name',
            'goods.storehouses_name',
            'goods.labels',
            'goods.created_at',
            'user_level_goods.price',
            'user_level_goods.goods_weight',
            'user_level_goods.goods_lock_weight',
            'factorys.thumb'
        );


        $res= DB::table('orders')
            ->Join('order_goods', 'orders.id','=', 'order_goods.order_id')
            ->Join('goods','goods.id','=','order_goods.goods_id')
            ->Join('user_level_goods','user_level_goods.goods_id','=','order_goods.goods_id')
            ->join('factorys','goods.factorys_id','=','factorys.id')
            ->where($where)
            ->select($select)
            ->distinct('goods.id')
            ->limit($limit)
            ->get()
            ->toArray();



        return $res;

    }

    public function del($id){
        DB::beginTransaction();
        $res = $this->goods->select('id',$id)->update(array('status'=>3));
        if($res){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }
    }


    //关键字 联想提示
    public function keyword_like($param){



        $where[] = ['name', 'like' , '%'.$param['keyword'].'%'];

        $res = DB::table('categorys')->where($where)->where('parent_id','!=',0)->limit(10)->orderBy('order','desc')->get();



        return $res;



    }


    //指数 自动同步方法
    public function index_data($param=''){

        $now_time=time();

        $param['day']=intval($param['day']);

        if($param['day']!=0){

            $startDate = $now_time - $param['day']*24*60*60; //最近3天数据
            $startDate= date('Y-m-d',$startDate);

        }else{

            $startDate = $now_time - 3*24*60*60; //最近3天数据
            $startDate= date('Y-m-d',$startDate);
        }

        $endDate= date('Y-m-d',$now_time);


        $param = array(
            'startDate'=>$startDate,
            'endDate'=>$endDate
        );

        $urllist=array(

            'http://118.190.65.127:7878/goldenbdp/app/kuaigangAjax!executeTXIndice.do',
            'http://118.190.65.127:7878/goldenbdp/app/kuaigangAjax!executeIndice.do'
        );
        $url='';

        $where_all_data=array($startDate,$endDate);

        $all_data= DB::table('index_data')->whereBetween('steelDate', $where_all_data)->get()->toArray();

        $i=0;
        $insert_data=array();

        for ($i_f=0;$i_f<2;$i_f++){

            $url= $urllist[$i_f];
            $res=CURL_POST($url,$param);
            $res=json_decode($res,true);

            $list= $res;

            foreach ($list as $v){

                $is_set=false;

                foreach ($all_data as $all_v){

                    if(strtotime($v['steelDate']) == strtotime($all_v->steelDate) && $v['indiceType']==$all_v->indiceType && $v['indiceArea']==$all_v->indiceArea){

                        $is_set=true;
                    }
                }

                if(!$is_set){

                    $insert_data[$i]['steelZdf']=$v['steelZdf'];
                    $insert_data[$i]['steelZde']=$v['steelZde'];
                    $insert_data[$i]['steelRemark']=$v['steelRemark'];
                    $insert_data[$i]['steelDate']=$v['steelDate'];
                    $insert_data[$i]['indiceType']=$v['indiceType'];
                    $insert_data[$i]['indiceArea']=$v['indiceArea'];
                    $insert_data[$i]['steelValue']=$v['steelValue'];
                    $i++;

                }
            }

        }

        echo 'insert data list <br/><pre>';

        print_r($insert_data);
        echo '<pre>';

        DB::table('index_data')->insert($insert_data);








    }


    /**
     * 获取指数接口
     * @author zhangchuang
     * @version 1.0.0
     *
     */


    public function index_api($param){


            $day = intval($param['day']);


            $where_type=array();

            if(isset($param['indiceType'])){
                if($param['indiceType'] == '0'){
                    $where_type=array('index_data.indiceType'=>' ');
                }elseif($param['indiceType'] == '1'){
                    $whereIn_type=array(1,2,3);
                }else{
                    $where_type=array('index_data.indiceType'=>$param['indiceType']);
                }

            }
            if($param['indiceArea']){
                $where_area=array('index_data.indiceArea'=>$param['indiceArea']);
            }
            $where_area = $where_area??'';

            $endTime =  date('Y-m-d H:i:s',time());
            $startTime= date('Y-m-d H:i:s',time()- (60*60*24*$day));

            $select=array('index_data.*','index_area.name as indiceArea_name');

            $where=array($startTime,$endTime);

            if(!empty($param['data_type'])){
                    $obj =  DB::table('index_data')
                        ->leftJoin('index_area','index_area.indiceAreaid','=','index_data.indiceArea')
                        ->orderBy('index_data.steelDate','ASC')
                        ->orderBy('index_data.id','desc')
                        ->where($where_type);

            }else{
                if(isset($whereIn_type)){
                    $obj =  DB::table('index_data')
                            ->leftJoin('index_area','index_area.indiceAreaid','=','index_data.indiceArea')
                            ->where($where_type)
                            ->whereIn('index_data.indiceType',$whereIn_type)
                            ->orderBy('index_data.steelDate','ASC')
                            ->orderBy('index_data.indiceArea','desc');
                }else{
                    $obj =  DB::table('index_data')
                            ->leftJoin('index_area','index_area.indiceAreaid','=','index_data.indiceArea')
                            ->where($where_type)
                            ->orderBy('index_data.steelDate','ASC')
                            ->orderBy('index_data.indiceArea','desc');
                    }

            }
            if($where_area){
                $obj = $obj->where($where_area);
            }
            $res = $obj->whereBetween('index_data.steelDate', $where)
                ->select($select)
                ->get();

            return $res;



    }

}

