<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class WebviveController extends Controller
{
    //
    /**
     *
     * webview 代理层
     * 张闯
     *
     *
     */
    public function index(Request $request){

        $param=$request->all();
        $setparam['url'] =  $param['url'];
        check_param($setparam);


        $url=urldecode($setparam['url']);
        $data['url']=   $url;


        return view('webview',$data);


    }


    public function use_html(Request $request){


        return view('html/use_html');


    }



}
