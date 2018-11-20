<?php
namespace App\Services;

use App\Models\Tourist;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;

/**
 * 公司service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class TouristService
{
    public $tourist;

    /**
     * 对象实例
     * @param
     */
    public function __construct()
    {
        $this->tourist = new Tourist;
    }
    /**
     * 保存游客信息
     * @author lvqing@kuaigang.net
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function createTourist($data){


        $res = $this->tourist->where('wx_openid', $data['wx_openid'])->first();

        if(empty($res)){

            $data['current_login_time'] = time();
            $res = $this->tourist->create($data);
        }

        return $res;
    }
    /**
     * 通过ID获得游客表信息
     * @author lvqing@kuaigang.net
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getInfo($id){
        $res = $this->tourist->find($id);
        return $res;
    }

    public function deleteTourist($id){
        $res = $this->tourist->where('id' , $id)->delete();
        return $res;
    }

}

