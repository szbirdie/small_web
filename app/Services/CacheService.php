<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

/*
 *
 * 缓存服务层
 * zhangchuang
 * 2018-0919
 *
 */
class CacheService
{


    public function __construct()
    {

    }


    /*
     *
     * 写缓存
     *
     */

    public static function setCache(string $key,$value,$minutes=60*24,$pid=0){



        Cache::put($key, $value, $minutes);

        if($pid!=0){

            Self::cache_manage($key,$pid);

        }

        return true;

    }

    public static function cache_manage($key='',$pid=''){

        try {

            if(!empty($key)){

                $insertdata=array(
                    'key'=>$key,
                    'parent_id'=>$pid

                );
                DB::table('cache_list')->insert($insertdata);

            }

            return true;

        }catch(\Exception $e){

            return false;
        }
    }


    /*
    *
    * 取缓存
    *
    */
    public static function getCache(string $key){

        //检查缓存是否存在
        if (Cache::has($key)) {

            return $value = Cache::get($key);

        }else{

            return null;


        }


    }


    /*
    *
    * 删缓存
    *
    */

    public static function delCache(string $key,string $type=''){


        if(empty($type)){

            Cache::forget($key);
        }else{

            Cache::flush();
        }


        return true;

    }









}

