<?php
namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * 分类service
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class CategoryService
{
    public $category;

    /**
     * 注入UserLevel 对象实例
     * @param 
     */
    public function __construct()
    {
        $this->category = new Category();
    }
    
}

