<?php
namespace App\Services;


use Illuminate\Support\Facades\Cache;
use App\Models\AdminConfig;
use Illuminate\Support\Facades\Log;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * 配置文件service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class AdminConfigService
{
    protected $adminConfig;
    /**
     * 注入UserLevel 对象实例
     * @param
     * @author zdk 317583717@qq.com
     * @version 1.0.0
     */
    public function __construct()
    {
        $this->adminConfig = new AdminConfig();
    }

    /**
     * 取得config字段,如果结果为'' 空字符串,说明这个key没有在后台进行设置
     * @param string $key
     * @return string
     * @author zhangchuang 0919
     * @version 1.0.0
     */
    public static function getConfig(string $key){


        if (Cache::has($key)) {  //存在


            return CacheService::getCache($key);

        }else{ //不存在

            $adminConfig = new AdminConfig();
            $config = $adminConfig->where('name','=',$key)->first();
            if(!$config){
                return '';

            }else{

                CacheService::setCache($config->name,$config->value);
                return $config->value;
            }
        }
    }


    /**
     * 清空config 缓存，只清空，不作重新缓存
     * @return boolean
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public static function resetConfig(){
        return Cache::tags('adminConfig')->flush();
    }


    /**
     * 后台设定休市时间
     * @author lvqing@kuaigang.net
     * @return string
     */
    public static function marketStatus(Request $request){
        $update = array();
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        if (empty($startTime) || empty($endTime)) {
            return false;
        }
        $update['description'] = $startTime.','.$endTime;
        return AdminConfig::where(['name'=>'market_status'])->update($update);
    }


   /**
     * 后台设定获得休市时间
     * @author lvqing@kuaigang.net
     * @return string
     */
    public static function getMarketInfo(Request $request){
        $where = array();
        $name = $request->get('name');
        if (empty($name)) {
            return false;
        }
        $where['name'] = $name;
        return AdminConfig::where($where)->first();
    }


   /**
     * 市场 开关
     * @author lvqing@kuaigang.net
     * @return string
     */
    public static function marketSwitch(Request $request){
        $update = array();
        $value = $request->get('value');
        if (empty($value)) {
            return false;
        }
        $update['value'] = $value;
        return AdminConfig::where(['name'=>'market_status'])->update($update);
    }

}

