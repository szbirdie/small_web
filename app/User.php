<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User
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
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessTokenTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLoginNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereOauthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRelatorPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserHeadimg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserStatus($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
