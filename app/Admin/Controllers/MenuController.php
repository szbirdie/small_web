<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/14
 * Time: 9:53
 */

namespace App\Admin\Controllers;

use Illuminate\Routing\Controller;

class MenuController extends Controller
{
    /**
     * 菜单真删
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function del($id){
        $res = \DB::table('admin_menu')->where('id',$id)->delete();
        if($res){
            return response()->json([
                'status'  => true,
                'message' => '删除成功 !',
            ]);
        }else{
            return response()->json([
                'status'  => false,
                'message' => '删除失败 !',
            ]);
        }
    }
}