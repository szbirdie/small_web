<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/24
 * Time: 17:53
 */

namespace App\Services;

use App\Models\IndexData;

class IndexDataService
{
    public $index_data;

    /**
     * 注入IndexData 对象实例
     */
    public function __construct()
    {
        $this->index_data = new IndexData;
    }

}