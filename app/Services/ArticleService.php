<?php
namespace App\Services;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Services\CacheService;

/**
 * 文章service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class ArticleService
{
    public $article;

    /**
     * 注入UserLevel 对象实例
     * @param
     */
    public function __construct()
    {
        $this->article = new Article;
    }

    /**
     * 获取文章列表数据
     * @author lvqing@kuaigang.net
     * @param $perPage 一行显示多少条
     * @param $page 目前页数
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */
    public function getList($perPage, $page, $where = array(), $select = '*'){
        //获取第一页的缓存
        if ($page == 1) {
            $article_list = CacheService::getCache(config('cache.cache_name.article_list'));
        }
        if (empty($article_list)) {

            $article_list = $this->article->select($select)
                ->where($where)
                ->where('articles.cate_id', '!=', config('app.article_cate_id'))
                ->join('article_cate as ca','articles.cate_id','=','ca.id')
                ->orderBy('articles.id', 'desc')
                ->paginate($perPage);

            if ($page == 1) {
                $article_list_json = json_encode($article_list);
                CacheService::setCache(config('cache.cache_name.article_list'),$article_list_json,10);
            }
        }else{
            $article_list = json_decode($article_list);
        }
		return $article_list;
    }

    /**
     * 获取首页通知列表
     * @author lvqing@kuaigang.net
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */
    public function getIndexList($where, $select = '*'){
        //获取第一页的缓存
        $article_index_list = CacheService::getCache(config('cache.cache_name.article_index_list'));
        if (empty($article_index_list)) {

            $article_index_list = $this->article->select($select)->where($where)->orderBy('id', 'desc')->get();
            $article_index_list_json = json_encode($article_index_list);
            CacheService::setCache(config('cache.cache_name.article_index_list'),$article_index_list_json,10);
        }else{
            $article_index_list = json_decode($article_index_list);
        }
		return $article_index_list;
    }

    /**
     * 获取推荐咨询列表
     * @author lvqing@kuaigang.net
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */

    public function getRecomandList($where, $select = '*', $leftJoin = ''){
        if ($leftJoin != 'article_cate') {
            $article_index_list = $this->article->select($select)->where($where)->take(5)->orderBy('updated_at', 'desc')->get()->toArray();

        }else{
            $article_index_list = $this->article->select($select)->where($where)->where('cate_id', '>', 1)->leftJoin('article_cate','articles.cate_id','=','article_cate.id')->take(5)->orderBy('articles.updated_at', 'desc')->get()->toArray();
        }


        return $article_index_list;
    }
    /**
     * 获取文章详情
     * @author lvqing@kuaigang.net
     * @param $where 筛选条件
     * @param $select 显示对应字段
     * @param $request
     */
    public function getArticleInfo($article_id, $select = '*'){
    	return $this->article->select($select)->join('article_cate as ca','articles.cate_id','=','ca.id')->find($article_id);
    }

}

