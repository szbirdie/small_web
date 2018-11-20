<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Scopes\StateScope;

/**
 * App\Models\User
 *
 * @property int $id 主键
 * @property int $user_status 用户状态  1、开启 2、关闭 ；用户状态默认为1
 * @property string $last_login_ip 上次登录ip
 * @property int $login_num 登录次数
 * @property int $last_login_time 上次登录时间
 * @property string $token 授权token
 * @property int $access_token_time 授权时间
 * @property int $oauth_id 授权服务器用户ID
 * @property int $company_id 企业ID
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $state 删除 :1正常 2删除 默认1
 * @property int $user_level_id 用户等级id
 * @property string $user_headimg 用户头像
 * @property string $user_name 用户昵称
 * @property string $company_name 公司简称
 * @property string $relator_phone 联系人电话
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAccessTokenTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLoginNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereOauthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRelatorPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserHeadimg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserStatus($value)
 * @mixin \Eloquent
 */
/**
 * 前台用户model
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class User extends Model
{
    //
    
    /**
     * 模型的“启动”方法.
     * 使用全局作用域进行软删除的设置
     * @return void
     */
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new StateScope());
    // }
    
    /**
     * 一对一关联公司用户绑定
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
        
    }
    
    /**
     * 一对一关联用户等绑定
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /*
     * 用户等级关联商品表
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id",
        "user_status",
        "last_login_ip",
        "login_num",
        "last_login_time",
        "token",
        "access_token_time",
        "oauth_id",
        "company_id",
        "user_level_id",
        "user_headimg",
        "user_name",
        "company_name",
        "relator_phone",
        "password",
        "name",
        "sales_id",
        "sales_name",
        "state",
        'identity_img',
        'real_auth_status',
        'register_type',
        'company_is_admin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         "created_at", "updated_at"
    ];

}
