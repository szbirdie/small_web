<?php

use Illuminate\Routing\Router;

/**
 * 后台管理路由
 * @version 1.0.0
 * @author zdk  317583717@qq.com
 */
Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->post('auth/menu/{id}/del', 'MenuController@del');
    $router->delete('auth/menu/{id}/del', 'MenuController@del');
    //用户管理资源路由
    $router->resource('users','UserController');
    //用户软删除
    $router->delete('users/{id}/del','UserController@del');
    //重置用户密码
    $router->post('users/{id}/resetPass','UserController@resetPass');
    //用户实名认证
    $router->get('user/{id}/check','UserController@check');
    //用户实名认证审核
    $router->post('user/{id}/save_check','UserController@saveCheck');

    //企业管理资源路由
    $router->resource('companys','CompanyController');
    //编辑公司的税务信息
    $router->resource('companys_taxation','CompanyTaxationController');
    //用户软删除
    $router->delete('companys/{id}/del','CompanyController@del');

    //认证公司资源路由
    $router->resource('company_temporary','CompanyTemporaryController');
    //认证公司审核
    $router->get('company_temporary/{id}/check/{type}','CompanyTemporaryController@check');
    //保存审核信息
    $router->post('company_temporary/{id}/check_save','CompanyTemporaryController@checkSave');
    //认证公司的税务信息
    $router->get('company_temporary_taxation/{id}/edit','CompanyTemporaryTaxationController@edit');
    //
    $router->resource('company_temporary_taxation','CompanyTemporaryController');



    //用户等级管理资源路由
    $router->resource('userlevels','UserLevelController');
    //用户软删除
    $router->delete('userlevels/{id}/del','UserLevelController@del');
    //订单管理路由
    $router->resource('order_list','OrderController');
    //订单待确定选择商品
    $router->get('order_goods','OrderController@order_goods');
    //后台订单 查看支付凭证
    $router->get('check_certificate','OrderController@check_certificate');   
    //后台订单 查看支付信息
    $router->get('check_certificate_info','OrderController@check_certificate_info');   
    $router->get('getSCategory','OrderController@getSCategory');   

    //后台用户管理公司分页路由
    $router->get('/api/companys','ApiController@getCompanys');
    //后台订单查询用户名称分页路由
    $router->get('/api/users','ApiController@getUsers');       
    //后台订单待确认 选择商品
    $router->get('/api/update_order_goods','ApiController@updateOrderGoods');
    //后台订单待确认 删除订单商品
    $router->get('/api/del_order_goods','ApiController@delOrderGoods');
    //后台订单待确认 提交确认
    $router->get('/api/confirm_order','ApiController@confirmOrder');
    //后台订单待确认 取消订单
    $router->get('/api/cancel_order','ApiController@cancelOrder');
    //后台用户管理用户等级分页路由
    $router->get('/api/userlevel','ApiController@getUserLevels');
    //后台待支付 完成支付
    $router->get('/api/success_payment','ApiController@successPayment');
    //后台待发货 确认发货
    $router->get('/api/success_consignment','ApiController@successConsignment');
    //后台待支付 审核失败
    $router->get('/api/auditing_error','ApiController@auditingError');    
    //后台待发货 保存支付凭证备注信息
    $router->get('/api/save_order_pay_info','ApiController@saveOrderPayInfo');  
    //后台待发货 添加物流信息
    $router->get('/api/add_logistics','ApiController@addLogistics');     
    //后台待确认 修改订单商品信息
    $router->get('/api/update_order_goods_info','ApiController@updateOrderGoodsInfo');   
    //后台删除所有缓存
    $router->get('/api/cache_flush','CacheController@cacheFlush');   

    // //订单管理路由
    // $router->resource('order_list','OrderController');
    //商品管理资源路由
    $router->resource('goods','GoodsController');
    //商品软删除
    $router->delete('goods/{id}/del','GoodsController@del');
    $router->delete('goods/{id}/change','GoodsController@change');

    $router->post('goods/import','GoodsController@import');
    //商品上传 导入
    $router->post('goods/upload','GoodsController@upload');

    $router->get('getSCategory', 'GoodsController@getSCategory');
    //文章列表
    $router->resource('article_list','ArticleController');
    //查看文章详情
    $router->get('article_info','ArticleController@articleInfo');
    //用户软删除 
    $router->delete('article_list/{id}/del','ArticleController@del');
    //文章列表
    $router->resource('article_categorys','ArticleCategorysController');
    //分类软删除
    $router->delete('article_categorys/{id}/del','ArticleCategorysController@del');
    $router->post('article_categorys/{id}/change','ArticleCategorysController@change');
    //分类编辑
    // $router->delete('article_categorys/{id}/edit','ArticleCategorysController@edit');
    $router->post('article_categorys/saveCate','ArticleCategorysController@saveCate');


    //banner管理路由
    $router->resource('banner_list','BannerController');
    //banner软删除
    $router->delete('banner_list/{id}/del','BannerController@del');

    //模块管理路由
    $router->resource('navigation_list','NavigationController');
    //模块管理软删除
    $router->delete('navigation_list/{id}/del','NavigationController@del');

    //热门搜索管理路由
    $router->resource('searches_hot','SearchesHotController');
    //热门搜索软删除
    $router->delete('searches_hot/{id}/del','SearchesHotController@del');

    //分类管理资源路由
    $router->resource('categorys','CategorysController');
    //分类软删除
    $router->post('categorys/{id}/change','CategorysController@change');
    $router->post('categorys/show','CategorysController@show');
    $router->post('categorys/saveCate','CategorysController@saveCate');
    $router->post('categorys/saveCate?id={id}','CategorysController@saveCate');

    //商品分配资源路由
    $router->resource('goods_distribute','GoodsDistributeController');
    $router->delete('goods_distribute/{id}/del','GoodsDistributeController@del');

//    $router->resource('goods_distribute/distribute','GoodsDistributeController');
    $router->post('goods_distribute/{id}/distribute','GoodsDistributeController@distribute');
    $router->post('goods_distribute/{id}/lock_weight_change','GoodsDistributeController@lockWeightChange');

    $router->post('goods_distribute/import','GoodsDistributeController@import');

    //发票管理资源路由
    $router->resource('invoice','InvoiceController');
    $router->post('invoice/show','InvoiceController@show');
    $router->delete('invoice/{id}/change','InvoiceController@change');
    $router->delete('invoice/{id}/cancel','InvoiceController@cancel');

    //推荐管理资源路由
    $router->resource('recommend','RecommendController');
    $router->delete('recommend/{id}/del','RecommendController@del');

    //推荐位管理资源路由
    $router->resource('recommend_position','RecommendPositionController');
    $router->delete('recommend_position/{id}/del','RecommendPositionController@del');

    //规格管理资源路由
    $router->resource('materials','MaterialsController');
    $router->delete('materials/{id}/del','MaterialsController@del');

    //材质管理资源路由
    $router->resource('spec','SpecController');
    $router->delete('spec/{id}/del','SpecController@del');

    //钢厂管理资源路由
    $router->resource('factorys','FactorysController');
    $router->delete('factorys/{id}/del','FactorysController@del');

    //仓库管理资源路由
    $router->resource('storehouses','StorehousesController');
    $router->delete('storehouses/{id}/del','StorehousesController@del');

    //休市配置
    $router->resource('set_close','AdminConfigController');
    //二维码
    $router->get('qr_code','AdminConfigController@qrCode');
    //获取二维码
    $router->get('get_qr_code','AdminConfigController@getQrCode');

    //缓存列表路由
    $router->resource('cache_list','CacheListController');
    //后台删除所有缓存
    $router->get('/api/cache_flush','CacheController@cacheFlush');
    //后台删除对应缓存
    $router->get('/api/clear_cache/{id}','CacheController@clearCache');

    //获得休市时间
    $router->get('/api/get_market_info','ApiController@getMarketInfo');
    //设置休市时间
    $router->get('/api/market_status','ApiController@marketStatus');
    //市场 开关
    $router->get('/api/market_switch','ApiController@marketSwitch');

});
