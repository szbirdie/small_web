<?php
namespace App\Services;

use App\Models\User;
use App\Models\UserRejectLog;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;

/**
 * 用户service
 * @author lvqing@kuaigang.net
 * @version 1.0.0
 */
class UserService
{
    public $user;
    public $userRejectLog;

    /**
     * 注入User 对象实例
     * @param
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function __construct()
    {
        $this->user = new User;
        $this->userRejectLog = new UserRejectLog();
    }

    /**
     * cas 用户注册
     * @param string $targetUrl
     * @param string $name
     * @param string $password
     * @param string $repassword
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casUserRegister(string $targetUrl,string $name, string $password ,string $repassword){
        $client = new Client();
//         $targetUrl = 'http://cas.test.kuaigang.net/api/v1/oauth/register';
        try {
            //发送请求
            $response = $client->request('POST', $targetUrl,[
                'query' =>[
                    'name' => $name,
                    'password' => $password,
                    'c_password' => $repassword,
                ]
            ]);

            $result = $response->getBody();
            //返回string
            $resultString  = (String)$result;
            if($response->getStatusCode() == 200){
                return $resultString;
            }else{
                //请求失败m有取得code
                return errorOutput('请求失败',400);
            }

        } catch (ClientException $e) {
            //异常报错
            return errorOutput('token error!',404);
        }
    }

    /**
     * cas 取得用户详情
     * @param string $targetUrl
     * @param string $name
     * @param string $accessToken
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casDetail(string $targetUrl,string $name, string $accessToken){

        $client = new Client();
//         $targetUrl = 'http://cas.test.kuaigang.net/api/v1/oauth/getDetails';
        try {
            //发送请求
            $response = $client->request('GET', $targetUrl,[
                'query' =>[
                    'name' => $name,
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => 'Bearer '.$accessToken
                ],
            ]);

            $result = $response->getBody();
            //返回string
            $resultString  = (String)$result;

            if($response->getStatusCode() == 200){
                return $resultString;
            }else{
                //请求失败m有取得code
                return errorOutput('请求失败',400);
            }
        } catch (ClientException  $e) {
            return errorOutput('token error!',404);
        }
    }

    /**
     * cas 修改用户密码
     * @param String $targetUrl
     * @param String $name
     * @param String $oldPassword
     * @param String $password
     * @param String $repassword
     * @param String $accessToken
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casUpdatePass(String $targetUrl, String $name, String $oldPassword , String $password, String $repassword, String $accessToken){
        $client = new Client();
//         $targetUrl = 'http://cas.test.kuaigang.net/api/v1/oauth/modifyPassword';
        try {
            //发送请求
            $response = $client->request('POST', $targetUrl,[
                'query' =>[
                    'name' => $name,
                    'old_password' => $oldPassword,
                    'password' => $password,
                    'c_password' => $repassword,
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => 'Bearer '.$accessToken
                ],
            ]);

            // $result = $response->getBody();
            //返回string
            // $resultString  = (String)$result;
            $resultString = json_decode((string) $response->getBody(), true);
            if($response->getStatusCode() == 200){
                return $resultString;
            }else{
                //请求失败m有取得code
                return errorOutput('请求失败',400);
            }
        } catch (ClientException $e) {
            return errorOutput('token error!',404);
        }
    }


    /**
     * cas 手机号是否已经注册
     * @param string $name
     * @param string $targetUrl
     * @return boolean
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casIsRegister(string $name,string $targetUrl){

        $client = new Client();
        try {
            //发送请求,body 请求
            $response = $client->request('POST', $targetUrl,[
                'form_params' =>[
                    'name' => $name,
                ],
            ]);
            $result = $response->getBody();
            //返回string
            $resultString  = (String)$result;

            if($response->getStatusCode() == 200){
                $data = json_decode($resultString);
//                 return $data;
                if($data->code==200){
                    //用户已经注册
                    return true;
                }else{
                    //用户没有注册
                    return false;
                }
            }else{
                //通讯失败
                return true;
            }
        } catch (ClientException $e) {
            //接口抛出了异常
            return true;
        }
    }


    /**
     * 重置cas用户密码
     * @param int $id
     * @param string $targetUrl
     * @return boolean
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casResetPass(int $id, string $targetUrl){
        $client =new Client();
        $user = $this->user->find($id);
        $token = $user->token;
        try {
            //发送请求,body 请求
            $response = $client->request('POST', $targetUrl,[
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
            ]);
            $result = $response->getBody();
            //返回string
            $resultString  = (String)$result;

            if($response->getStatusCode() == 200){
                $data = json_decode($resultString);
                //                 return $data;
                if($data->code==200){
                    //重置密码成功！
                    return true;
                }else{
                    return false;
                }
            }else{
                //通讯失败
                return false;
            }
        } catch (ClientException $e) {
            //接口抛出了异常
            return false;
        }
    }


    /**
     * cas 用户登录授权
     * @param string $targetUrl
     * @param string $name
     * @param string $accessToken
     * @return string
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function casLogin(string $targetUrl,string $name, string $password, $data,$code=''){
        $client = new Client();
        try {
            //发送请求
            $response = $client->request('POST', $targetUrl,[
                'query' =>[
                    'name'      => $name,
                    'password'  => $password,
                    'type'      => $data['type'],
                    'wx_openid' => $data['wx_openid'],
                    'wx_info'   => $data['wx_info'],
                    'code'      => $code
                ]
            ]);

            $resultString = json_decode((string) $response->getBody(), true);
            if($response->getStatusCode() == 200){
                return $resultString;
            }else{
                //请求失败m有取得code
                ShowApi(400,'','请求失败');
            }

        } catch (ClientException $e) {
            //异常报错
            ShowApi(404,'','系统异常');
        }
    }

    /**
     * 发送短信验证码
     * @param string $phone
     * @param int $type 1、注册 2、登录 3、改密码
     * @return mixed|string
     */
    public function casSendSmsCode(string $phone,int $type)
    {
        $client = new Client();
        $targetUrl = config('app.cas_url_prefix').'sms';
        try {
            //发送请求
            $response = $client->request('POST', $targetUrl,[
                'query' =>[
                    'type'   => $type,
                    'phone' => $phone,
                ]
            ]);
            $resultString = json_decode($response->getBody(), true);

            return $resultString;

        } catch (ClientException $e) {
            //异常报错
            ShowApi(1000,'','系统异常');
        }
    }


    /**
     * 更新用户信息
     * @author lvqing@kuaigang.net
     * @param  [type] $data          要更新的数据
     * @param  [type] $relator_phone 查询条件：用户手机号
     * @return string
     */
    public function casUpdate($data , $relator_phone){

        $res = $this->user->where(['relator_phone' => $relator_phone])->update($data);

        return $res;
    }

    /**
     * 软删除
     * @param int $id
     * @return \Eloquent
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function del(int $id){
        $data = $this->user->where('id',$id)->first();
        $data->state = 2;
        return $data->save();
    }

    /**
     * 活动User表用户信息
     * @author lvqing@kuaigang.net
     * @param  [array] $where 搜索条件
     * @return string
     */
    public function getUserInfo($where){
        $data = $this->user->where($where)->get()->first();
        if(!empty($data)){
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 更新用户token
     * @param int $id
     * @param string $token
     * @return boolean
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function updateToken(int $id, string $token){
       $userData = $this->user->find($id);
       $userData->token = $token;
       return $userData->save();
    }


    /**
     * 用户订单数量，用户信息等
     * @param $userInfo
     * @return mixed
     * author zhangchuang
     * author qyd 2018.11.16
     */
    public function get_user_info($userInfo)
    {

        $where=array(
            'company_id'=>$userInfo['company_id']
        );

        $temp_res= DB::table('orders')->where($where)->get();

        $order_confirm=0; //待确认
        $order_payment=0; //待付款
        $order_goods=0;   //待收货

        foreach ($temp_res as $v){

            if($v->order_status==0 || $v->order_status==1){
                $order_confirm++;
            }

            if($v->order_status==2){
                $order_payment++;
            }

        }

        $where=array(
            'orders.company_id'=>$userInfo['company_id']
        );

        $temp_res= DB::table('orders')
                   ->leftJoin('order_goods','orders.id','=','order_goods.order_id')
                   ->where($where)->get();


        foreach ($temp_res as $v){

            if($v->order_status==3 || $v->order_status==4){
                $order_goods++;
            }
        }

        $where=array(
            'id'=>$userInfo['user_id']
        );
        $temp_res= DB::table('users')->where($where)->get()->first();

        $userinfo_db['relator_phone']= $temp_res->relator_phone??'';
        $userinfo_db['company_name']= $temp_res->company_name??'';
        $userinfo_db['user_headimg']= $temp_res->user_headimg??'';
        $userinfo_db['user_name']= $temp_res->user_name??'';

        $data['orderconfirm']= $order_confirm;
        $data['orderpayment']= $order_payment;
        $data['ordergoods']=   $order_goods;
        $data['userinfo']= $userinfo_db;

        return $data;



    }
    /**
     * 用作后台订单选择用户名
     * @param Request $request
     * @return object
     */
    public function getUsers(Request $request){
        $q = $request->get('q');
        return $this->user->where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
    }


    /**
     * 获取单条用户驳回日志
     * @param $where
     * @param array $orderBy
     * @return mixed
     */
    public function getRejectLogOne($where,$orderBy=[])
    {
        $model = $this->userRejectLog->where($where);
        if($orderBy){
            $model->orderBy($orderBy[0],$orderBy[1]);
        }
        $data = $model->get()->first();
        if($data){
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 根据requestType获取注册来源
     * @param $requestType
     */
    public function getRegisterType($requestType)
    {
        $res = 'Unknown';
        switch($requestType){
            case 1: $res = 'Xcx';break;
            case 2: $res = 'Android';break;
            case 3: $res = 'IOS';break;
            case 4: $res = 'Other';break;
            case 5: $res = 'PC';break;
        }
        return $res;
    }
}

