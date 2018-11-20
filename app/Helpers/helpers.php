<?php

/**
 * 全局帮助函数
 * @version 1.0.0
 * @author zdk 317583717@qq.com
 */

if(!function_exists("successOutput")){

    /**
     * 成功返回帮助函数
     * @param array $data
     * @param int $code
     * @param string $msg
     * @return string
     */
    function successOutput(array $data , $msg='ok' , $code = 200 ){
        $result = array(
            'code' => $code,
            'message' => $msg,
            'result' => $data
        );
        return json_encode($result);
    }
}

if(!function_exists("errorOutput")){
    /**
     * 失败返回帮助函数
     * @param int $code
     * @param string $msg
     * @return string
     */
    function errorOutput( $msg='' , $code = 400){
        $result = array(
            'code' => $code,
            'message' => $msg,
            'result' => array(),
        );
        return json_encode($result);
    }
}

if(!function_exists("signature")){
    /**
     * 接口鉴权加密函数
     * @param array $data
     * @param string $nonce
     * @param string $secretKey
     * @return string
     */
    function signature(array $data , $nonce , $secretKey){
        ksort($data);
        $paramString = '';
        foreach ($data as $k=>$v){
            $paramString.=$k.$v;
        }
        $paramString.=$secretKey.$nonce;
        $md5String = md5($paramString);
        return $md5String;
    }
}

if(!function_exists("isMobile")){
    /**
     * 验证手机号是否正确
     * @param INT $mobile
     * @return boolean
     */
    function isMobile($mobile) {
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,3,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}

if(!function_exists("isEmail")){
    /**
     * 验证邮箱是否正确
     * @param $email
     * @return boolean
     */
    function isEmail($email) {
        return preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $email) ? true : false;
    }
}

if(!function_exists("imgUpload")){
    /**
     * 图片上传
     * @param $file
     * @return boolean
     */
    function imgUpload($file){
        if($file["error"]) {
            return $file["error"];
        }else {
            //没有出错
            //加限制条件
            //判断上传文件类型为png或jpg且大小不超过1024000B
            if(($file["type"]=="image/png"||$file["type"]=="image/jpeg")&&$file["size"]<1024000) {
                //防止文件名重复
                $filename ="./img/".time().$file["name"];
                //转码，把utf-8转成gb2312,返回转换后的字符串， 或者在失败时返回 FALSE。
                $filename =iconv("UTF-8","gb2312",$filename);
                //检查文件或目录是否存在
                if(file_exists($filename)) {
                    return"该文件已存在";
                } else {
                    //保存文件,   move_uploaded_file 将上传的文件移动到新位置
                    return move_uploaded_file($file["tmp_name"],$filename);//将临时地址移动到指定地址
                }
            }else {
                return "文件类型不对";
            }
        }
    }
}

if(!function_exists('dump')) {
    /**
     * 打印数据
     * @param mixed $data （可以是字符串，数组，对象）
     * @param  boolean $is_exit 是否退出程序，默认否
     */
    function dump($data, $is_exit = false){
        echo "<pre>";
        print_r($data);
        echo "</pre>";

        if($is_exit) exit();
    }
}

if(!function_exists("list_files")){
    /**
     * 列出目录内容
     * @param $dir
     */
    function list_files($dir) {
        if(is_dir($dir)) {
            if($handle = opendir($dir)) {
                while(($file = readdir($handle)) !== false) {
                    if($file != "." && $file != ".." && $file != "Thumbs.db") {
                        echo '<a target="_blank" href="'.$dir.$file.'">'.$file.'</a><br>'."\n";
                    }
                }
                closedir($handle);
            }
        }
    }
}

if(!function_exists("getRealIpAddr")){
    /**
     * 获取用户真实ip
     * @return string
     */
    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

if(!function_exists("force_download")){
    /**
     * 强制性文件下载
     * @param $file
     */
    function force_download($file) {
        if ((isset($file))&&(file_exists($file))) {
            header("Content-length: ".filesize($file));
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            readfile($file);
        }else {
            echo "No file selected";
        }
    }

}


if(!function_exists("ShowApi")){

    /**
     * 小程序api 输出统一函数
     * @param array $data
     * @param int $code
     * @param string $msg
     * @return string
     *
     * dapangdun 2018-09-12
     *
     */
    function ShowApi($code=0,$data='',$msg='' ){

        $result = array(
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        );
        header('Access-Control-Allow-Origin:*');
        header('Content-type: application/json');


        echo json_encode($result);
        exit();

    }
}





if(!function_exists("check_param")){

    /**
     * 小程序api 参数是否存在检测函数
     *
     * dapangdun 2018-09-12
     *
     */
    function check_param($param=array()){


        if(is_array($param)){

            foreach ($param as $k=>$v){

                if(!isset($param[$k])){

                    ShowApi(2,'','缺少参数'.$k);

                }

                if(empty($param[$k])){

                    ShowApi(2,'','缺少参数'.$k);
                }

            }

        }else{
            return false;
        }




    }
}

if(!function_exists("CheckNum")){
    /**
     * 页数判断
     * @param $num
     * @return string
     * @author zhao
     */
    function CheckNum($num){
        if(!is_numeric($num)) ShowApi('2','','num格式错误');
        if(!($num > 0)) ShowApi('2','','num不是合理数值');
        if(!($num <= 20)) ShowApi('2','','num不是合理数值');
    }
}

if(!function_exists("SMS_MESSAGE")){
    /**
     * 短信
     * @param $num
     * @return string
     * @author zhao
     */
    function SMS_MESSAGE($type,$sms_id,$to,$content=null,$businessid=null){
        $input = csrf_field()->toHtml();
        $start = 'value="';
        $end = '">';
        $token = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));
        switch($type){
            case 1:
                $content = $content ?? '1';
                $businessid = $businessid ?? 1;
                $url = env('SMS_NOTICE_URL').'?_token='.$token.'&SMS_ID='.$sms_id.'&businessid='.$businessid.'&to='.$to.'&content='.$content.'';
                break;
            case 2:
                $content = $content ?? mt_rand(100000,999999);
                $businessid = $businessid ?? 1;
                $url = env('SMS_VERIFY_URL').'?_token='.$token.'&SMS_ID='.$sms_id.'&businessid='.$businessid.'&to='.$to.'&content='.$content.'';
                break;
        }

        //初始化一个 cURL 对象
        $ch  = curl_init();
        //设置你需要抓取的URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //是否获得跳转后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        try{
            $response = curl_exec($ch);
            $response = json_decode($response,true);
            curl_close($ch);
        }catch (\Exception $e){
            return array('code'=>'0','message'=>'发送失败');
        }
        return $response;
    }
}

if(!function_exists("SMS_EMAIL")){
    /**
     * 邮件
     * @param $num
     * @return string
     * @author zhao
     */
    function SMS_EMAIL($title,$to,$content=null,$businessid=null){
        $input = csrf_field()->toHtml();
        $start = 'value="';
        $end = '">';
        $token = substr($input, strlen($start)+strpos($input, $start),(strlen($input) - strpos($input, $end))*(-1));

        $content = $content ?? '';
        $businessid = $businessid ?? rand(0,999);
        $url = env('SMS_EMAIL_URL').'?_token='.$token.'&title='.$title.'&businessid='.$businessid.'&to='.$to.'&content='.$content.'';

        //初始化一个 cURL 对象
        $ch  = curl_init();
        //设置你需要抓取的URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //是否获得跳转后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        try{
            $response = curl_exec($ch);
            curl_close($ch);
        }catch (\Exception $e){
            return array('code'=>'0','message'=>'发送失败');
        }
        return $response;
    }
}

//curl post  zhangchuang 2018-1017

if(!function_exists("CURL_POST")){


    function CURL_POST($content_url='',$param=array()){


        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$content_url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);



        $res = curl_exec($ch);
        curl_close ($ch);

        return $res;
    }

}

if(!function_exists("curlGet")){
    function curlGet($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }
}

//数组，或数组内对象 根据某个key 排序 zhangchuang 2018-1017

if(!function_exists("array_sort_help")){


    function array_sort_help($arr, $keys, $type = 'desc') {


        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){

            if(isset($v->$keys)){

                $keysvalue[$k] = $v->$keys;

            }else{

                $keysvalue[$k] = $v[$keys];
            }

        }

        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[] = $arr[$k];
        }
        return $new_array;

    }


}




