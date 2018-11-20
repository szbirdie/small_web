<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\UserService;
use App\Admin\Extensions\ResetPass;
use App\Admin\Extensions\SoftDelete;


/**
 * 后台会员管理
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class LvController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     * 搜索及列表页面
     * @return Content
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function index(Content $content)
    {
        return $content
            ->header('会员管理')
            ->description(trans('会员管理'))
            ->body($this->grid()->render());
    }

    /**
     * Show interface.
     * 详情页面
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('查看')
            ->description('查看')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     * 编辑页面
     * @param $id
     *
     * @return Content
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑')
            ->description('编辑')
            ->body($this->editForm()->edit($id));
    }

    /**
     * Create interface.
     * 添加页面
     * @return Content
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建')
            ->description('创建')
            ->body($this->createForm());
    }
    
    /**
     * Make a grid builder.
     * 列表方法
     * @return Grid
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    protected function grid()
    {
        $grid = Admin::grid(User::class, function(Grid $grid){
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 2, 'text' => '禁用', 'color' => 'default'],
            ];
            $grid->id('ID')->sortable();
            $grid->relator_phone('用户名')->sortable();
            $grid->user_name('用户昵称')->sortable();
//             $grid->company_name('公司名称')->sortable();
            $grid->column('company.company_name','公司名称')->sortable();
//             $grid->user_level_id('用户等级')->sortable();
            $grid->column('userLevel.level_name','用户等级')->sortable();
            $grid->user_status('用户状态')->switch($states);
            
            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            
            $grid->filter(function($filter){
                
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                
                // 在这里添加字段过滤器
                $filter->like('relator_phone', '用户名');
                $filter->like('user_name', '用户昵称');
                $filter->equal('company_id','公司名称')->select(function($id){
                    $company = Company::find($id);
                    if ($company) {
                        return [$company->id => $company->company_name];
                    }
                })->ajax('/admin/api/companys');
                
                $filter->equal('user_level_id','用户等级')->select(function ($id) {
                    $userLevel = UserLevel::find($id);
                    if ($userLevel) {
                        return [$userLevel->id => $userLevel->level_name];
                    }
                })->ajax('/admin/api/userlevel');
                
            });
            
            
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                // prepend一个操作
                $actions->prepend(new ResetPass($actions->getKey()));
                $actions->append(new SoftDelete($actions->getKey(),$actions->getResource()));
            });
            
        });
        
        return $grid;
    }
    
    
    /**
     * Make a show builder.
     * 查看详情方法
     * @param mixed $id
     *
     * @return Show
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));
        $show->id('ID');
        $show->relator_phone('用户名');
        $show->user_name('用户昵称');
//         $show->company('公司名称',function($company){
//             $company->setResource('/admin/users');
//             $company->company_name();
            
//         });
        $show->company()->company_name('公司名称');
//         $show->user_level_id('用户等级');
        $show->userlevel()->level_name('用户等级');
        $show->login_num('登录次数');
        
        $show->last_login_ip('登陆IP');
        $show->created_at('添加时间');
        $show->updated_at('修改时间');
        
        return $show;
    }

    /**
     * 列表对应的form.
     *
     * @return Form
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function form()
    {
        $states = [
            'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
            'off' => ['value' => 2, 'text' => '禁用', 'color' => 'default'],
        ];
        
        $form = new Form(new User());
        $id = $form->id;
        $form->display('id', 'ID');
        $form->text('relator_phone', '用户名')->rules('required|regex:/^[1][3-9][0-9]{9}$/|unique:users');
        $form->text('user_name', '用户昵称')->rules('required');
        $form->password('password','密码')->rules('required|min:6|max:10');
//         $form->text('company_name', '公司名称')->rules('required');
        $form->select('company_id', '公司名称')->options(
            function ($id) {
                $company = Company::find($id);
                if ($company) {
                    return [$company->id => $company->company_name];
                }
           }
        )->ajax('/admin/api/companys')->rules('required');
        
       $form->select('user_level_id', '用户等级')->options(
           function ($id) {
               $userLevel = UserLevel::find($id);
               if ($userLevel) {
                   return [$userLevel->id => $userLevel->level_name];
               }
           }
       )->ajax('/admin/api/userlevel')->rules('required');
        
        $form->switch('user_status', '用户状态')->states($states);
        
        // $form->saving(function (Form $form) {
        //     Log::info('form',['form'=>$form]);
        //     abort(404);
        //     //修改密码
        //     $userService = new UserService(new User());
        //     $userName = $form->relator_phone;
        //     if(empty($userName)){
        //         //修改
        //         $targetUrl = config('app.cas_url_prefix').'modifyPassword';
        //         $user = User::find($form->model()->id);
        //         $updatePass = $userService->casUpdatePass($targetUrl, $user->relator_phone, $user->password, $form->password, $form->password, $user->token);
        //     }
        // });
        
        $form->saved(function (Form $form) {
            $userService = new UserService(new User());
            $userName = $form->relator_phone;
            $token = '';
            if(!empty($userName)){
                $targetUrl = config('app.cas_url_prefix').'verificationUser';
                //添加
                //把用户信息存入cas
                //1已经注册过cas 3 本地未保存  0 用户名为空  2 接口逻辑错误
                $isUserRegitstered = $userService->casIsRegister($form->relator_phone,$targetUrl);
                if(!$isUserRegitstered){
                    //cas 中不存在这个用户
                    $targetUrlReg = config('app.cas_url_prefix').'register';
                    $res = $userService->casUserRegister($targetUrlReg, $form->relator_phone, $form->password, $form->password);
                    $data = json_decode($res);
                    if($data->code == 200){
                        $token = $data->result->token;
                    }
                }else{
                    //cas 中已经存在这个用户 登陆一下取得用户的token并更新业务服务器的用户的token
                    $targetUrlLogin = config('app.cas_url_prefix').'login';
                    $casUser = $userService->casLogin($targetUrlLogin, $form->relator_phone, '123456');
                    $casUserJson = json_decode($casUser);
                    $token = $casUserJson->result->token;
                }
                //更新用户token
                $userService->updateToken($form->model()->id, $token);
            }
        });          
        
        return $form;
    }
    
    /**
     * 修改用户信息表单.
     *
     * @return Form
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function editForm()
    {
        $form = new Form(new User());
        
        $form->display('id', 'ID');
        $form->display('relator_phone', '用户名');
//         $form->text('relator_phone', '用户名')->rules('required|regex:/^[1][3-9][0-9]{9}$/|unique:users');
        $form->text('user_name', '用户昵称')->rules('required|max:10');
        $form->password('password','密码')->rules('required|min:6|max:10');
        //         $form->text('company_name', '公司名称')->rules('required');
        $form->select('company_id', '公司名称')->options(
            function ($id) {
                $company = Company::find($id);
                if ($company) {
                    return [$company->id => $company->company_name];
                }
            }
        )->ajax('/admin/api/companys')->rules('required');
            
        $form->select('user_level_id', '用户等级')->options(
            function ($id) {
                $userLevel = UserLevel::find($id);
                if ($userLevel) {
                    return [$userLevel->id => $userLevel->level_name];
                }
            }
        )->ajax('/admin/api/userlevel')->rules('required');
                
        $form->saving(function (Form $form) {
        });
                
        return $form;
    }
    
    
    /**
     * 创建用户信息表单.
     *
     * @return Form
     * @version 1.0.0
     * @author zdk  317583717@qq.com
     */
    public function createForm()
    {
        $form = new Form(new User());
        
        $form->display('id', 'ID');
        $form->text('relator_phone', '用户名')->rules('required|regex:/^[1][3-9][0-9]{9}$/|unique:users');
        $form->text('user_name', '用户昵称')->rules('required|max:10');
        $form->password('password','密码')->rules('required|min:6|max:10');
        //         $form->text('company_name', '公司名称')->rules('required');
        $form->select('company_id', '公司名称')->options(
            function ($id) {
                $company = Company::find($id);
                if ($company) {
                    return [$company->id => $company->company_name];
                }
            }
        )->ajax('/admin/api/companys')->rules('required');
            
        $form->select('user_level_id', '用户等级')->options(
            function ($id) {
                $userLevel = UserLevel::find($id);
                if ($userLevel) {
                    return [$userLevel->id => $userLevel->level_name];
                }
            }
        )->ajax('/admin/api/userlevel')->rules('required');
        $form->hidden('token');        
        
        return $form;
   }
   
   /**
    * 软删除
    * @param int $id
    * @return \Illuminate\Http\JsonResponse
    * @version 1.0.0
    * @author zdk  317583717@qq.com
    */
   public function del(int $id){
       $userService = new UserService(new User());
       $userService->del($id);
       return response()->json([
           'status'  => true,
           'message' => '删除成功 !',
       ]);
   }
   
   /**
    * 重置密码
    * @param int $id
    * @return \Illuminate\Http\JsonResponse
    * @version 1.0.0
    * @author zdk  317583717@qq.com
    */
   public function resetPass(int $id){
       $userService = new UserService(new User());
       $targetUrl = config('app.cas_url_prefix').'resettingPassword';
       $res = $userService->casResetPass($id, $targetUrl);
       if($res){
           return response()->json([
               'status'  => true,
               'message' => '重置成功 !',
           ]);
       }else{
           return response()->json([
               'status'  => false,
               'message' => '重置失败 !',
           ]);
       }
   }
   
   
}
