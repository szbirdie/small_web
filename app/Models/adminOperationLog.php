<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class adminOperationLog extends Model
{
    //
    /**
     * 系统设置表
     *
     * @var string
     */
    protected $table = 'admin_operation_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id", "user_id", "path", "method", "ip", "input", "created_at", "updated_at"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
