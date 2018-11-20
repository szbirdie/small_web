<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/14
 * Time: 15:39
 */

namespace App\Services;

use App\Models\LogOrderOperation;
use Illuminate\Http\Request;

class LogOrderOperationService
{
    public $log_order_operation;

    /**
     * 注入order_goods 对象实例
     * @param
     */
    public function __construct(LogOrderOperation $log_order_operation)
    {
        $this->log_order_operation = $log_order_operation;
    }

    /**
     * 订单操作记录
     * @author zhao
     * @param array $data
     * @return boolean
     */
    public function writelog($data){
        return $this->log_order_operation->insert($data);
    }
}