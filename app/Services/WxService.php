<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;


/**
 * 微信服务类
 * @author qingyongdong@kuaigang.net
 */
class WxService
{
    /**
     * 初始化
     */
    public function __construct()
    {

    }

    /**
     * 获取微信access_token
     * @return string accessToken
     */
    public static function getAccessToken()
    {
        $cacheKey='wx_access_token';
        $accessToken=CacheService::getCache($cacheKey);
        if(empty($accessToken)){
            $appId  = config('wxxcx.appid');
            $secret = config('wxxcx.secret');
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appId.'&secret='.$secret;
            $res = json_decode(curlGet($url));

            if(!empty($res->access_token)){
                $accessToken=$res->access_token;
                CacheService::setCache($cacheKey,$res->access_token,60);
            }
        }
        return $accessToken;
    }

    /**
     * 生成二维码
     * @param array $param
     * @return mixed
     */
    public static function createQrCode(array $param)
    {
        $accessToken = self::getAccessToken();
        $url         = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$accessToken;
        $qrCode      = self::curlPost($url,$param);
        return $qrCode;
    }

    /**
     * curl post
     * @param string $url
     * @param array $param
     * @return mixed
     */
    public static function curlPost(string $url,array $param)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        $res = curl_exec($ch);
        curl_close ($ch);
        return $res;
    }
}

