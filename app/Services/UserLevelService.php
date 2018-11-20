<?php
namespace App\Services;

use App\Models\UserLevel;
use Illuminate\Http\Request;

/**
 * 公司service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class UserLevelService
{
    public $userLevel;

    /**
     * 注入UserLevel 对象实例
     * @param 
     */
    public function __construct(UserLevel $userLevel)
    {
        $this->userLevel = $userLevel;
    }
    
    /**
     * 用作后台创建用户选择等级
     * @param Request $request
     * @return object
     */
    public function getUserLevels(Request $request){
        $q = $request->get('q');
        return $this->userLevel::where('level_name', 'like', "%$q%")->paginate(null, ['id', 'level_name as text']);
    }
    
    /**
     * 软删除
     * @param int $id
     * @return \Eloquent
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function del(int $id){
        $data = $this->userLevel->where('id',$id)->first();
        $data->state = 2;
        return $data->save();
    }
    
    
}

