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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\AdminRole;
use App\Models\UserRejectLog;
use App\Services\UserService;
use App\Admin\Extensions\ResetPass;
use App\Admin\Extensions\SoftDelete;
use App\Admin\Extensions\ExcelExpoter;
use Illuminate\Support\MessageBag;

/**
 * 后台会员管理
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class UserController extends Controller
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
            ->header('客户管理')
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
            ->header('查看客户信息')
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
            ->header('编辑客户信息')
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
            ->header('创建客户')
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
            if (Admin::user()->id != 1) {
                $grid->model()->where('state', '!=', 2)->where('sales_id', Admin::user()->id)->orderBy('id', 'desc');
            }else{
                $grid->model()->where('state', '!=', 2)->orderBy('id', 'desc');
            }

            
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 2, 'text' => '禁用', 'color' => 'default'],
            ];
            $grid->id('ID')->sortable();
            $grid->relator_phone('用户名(手机号)')->sortable();
            $grid->user_name('客户姓名')->sortable();
            $grid->column('company.company_name','企业名称')->sortable();
            $grid->column('userLevel.level_name','用户等级')->sortable();
            $grid->user_status('是否启用')->switch($states);
            $grid->state('状态')->display(function ($state) {
                if($state == 1){
                    return '初始创建';
                }elseif($state == 3){
                    return '已授权';
                }else{
                    return '未知';
                }
            });


            $grid->real_auth_status('认证状态')->grend()->using(['1'=>'通过','2'=>'驳回',3=>'待审核',4=>'未认证']);
            //像模板传递参数
            // $grid->setView('admin::grid.table', ['users' => array(1,2)]);

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $relator_phone = $_GET['relator_phone'] ?? '';
            $user_name = $_GET['user_name'] ?? '';
            $company_id = $_GET['company_id'] ?? '';
            $user_level_id = $_GET['user_level_id'] ?? '';

            $export_obj = User::where('users.state','!=',1)
                ->where('relator_phone','like','%'.$relator_phone.'%')
                ->where('user_name','like','%'.$user_name.'%');
            if($company_id){
                $export_obj->where('company_id',$company_id);
            }
            if($company_id){
                $export_obj->where('user_level_id',$user_level_id);
            }

            $export_arr = User::where('users.state','!=',1)->get()->toArray();
            $excel = new ExcelExpoter();
            $excel->setAttr(
                ['用户名','客户姓名', '企业名称', '用户等级','是否启用','状态'],
                ['user_name','name', 'company_name', 'level_name','user_status', 'state'],
                $export_arr,
                '商品分配模板','商品');
            $grid->exporter($excel);
            
            $grid->filter(function($filter){
                
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                
                // 在这里添加字段过滤器
                //用户名和客户姓名做下拉框的话就不能模糊搜索了
                //需要的话可以启用
//                $user = User::select('relator_phone','user_name','id')->get()->toArray();
//                $user_phone[' '] = '全部';
//                $user_name[' '] = '全部';
//                foreach($user as $u){
//                    $user_phone[$u['id']] = $u['relator_phone'];
//                    $user_name[$u['id']] = $u['user_name'];
//                }

                $company = Company::select('company_name','id')->where('state',1)->get()->toArray();
                $company_arr[' '] = '全部';
                foreach($company as $c){
                    $company_arr[$c['id']] = $c['company_name'];
                }

                $user_level = UserLevel::select('level_name','id')->where('state',1)->where('status',1)->get()->toArray();
                $user_level_arr[' '] = '全部';
                foreach($user_level as $u_l){
                    $user_level_arr[$u_l['id']] = $u_l['level_name'];
                }

                $filter->like('relator_phone', '用户名');
                $filter->like('user_name', '客户姓名');
                $filter->equal('company_id','企业名称')->select($company_arr);
                $filter->equal('user_level_id','用户等级')->select($user_level_arr);
                
            });
            
            
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
                // prepend一个操作
                $actions->prepend(new ResetPass($actions->getKey()));
                $actions->prepend('<a style="padding-right:4px" href="user/'.$actions->row->id.'/check"><i class="fa btn btn-xs btn-success">实名认证审核</i></a>');
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
        $show->relator_phone('用户名(手机号)');
        $show->user_name('客户姓名');
//         $show->company('企业名称',function($company){
//             $company->setResource('/admin/users');
//             $company->company_name();
            
//         });
        $show->company()->company_name('企业名称');
//         $show->user_level_id('用户等级');
        $show->userlevel()->level_name('用户等级');
        $show->login_num('登录次数');
        
        $show->last_login_ip('登陆IP');
        $show->created_at('添加时间');
        $show->updated_at('修改时间');
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });
        
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
        $form->text('relator_phone', '用户名(手机号)')->rules(['required','regex:/^1\d{10}$/','unique:users'], [
            'required'=>'用户名必填',
            'regex'=>'格式错误',
            'unique'=>'该用户名已存在',
        ]);
        $form->text('user_name', '客户姓名')->rules('required|regex:/[\p{Han}A-Za-z]/u',[
            'required'=>'客户姓名必填',
            'regex'=>'格式有误',
            ]);
        $form->password('password','密码')->rules('required|min:6|max:10', [
            'required'=>'密码必填',
            'min'=>'长度不得小于6',
            'max'=>'长度不得大于10',
            ]);
        $form->text('identity_card', '身份证号')->rules(['required','regex:/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|[xX])$/','min:15','max:18'], [
            'required'=>'身份证号必填',
            'regex'=>'格式有误',
            'min'=>'长度不得小于15',
            'max'=>'长度不得大于18',
            ]);

        $form->select('company_id', '企业名称')
        ->options(Company::where(['state'=>1])
        ->orderBy('id','desc')
        ->get()->pluck('company_name', 'id'))->rules('required',['required'=>'企业名称必选']);
        $form->select('user_level_id', '用户等级')
        ->options(UserLevel::where(['status'=>1,'state'=>1])
        ->get()->pluck('level_name', 'id'))->rules('required',['required'=>'用户等级必选']);
        $form->select('sales_id', '所属销售')->options(AdminRole::where(['admin_roles.slug'=>'sales'])
            ->select('admin_users.username as username', 'admin_users.id as id')
            ->join('admin_role_users','admin_roles.id','=','admin_role_users.role_id')
            ->join('admin_users','admin_role_users.user_id','=','admin_users.id')
            ->orderBy('admin_users.id', 'desc')
            ->get()->pluck('username', 'id'))->rules('required',['required'=>'所属销售必选']);
        // $form->select('company_id', '企业名称')->options(
        //     function ($id) {
        //         $company = Company::find($id);
        //         if ($company) {
        //             return [$company->id => $company->company_name];
        //         }
        //     }
        // )->ajax('/admin/api/companys')->rules('required');
        // $form->select('user_level_id', '用户等级')->options(
        //     function ($id) {
        //         $userLevel = UserLevel::find($id);
        //         if ($userLevel) {
        //             return [$userLevel->id => $userLevel->level_name];
        //         }
        //     }
        // )->ajax('/admin/api/userlevel')->rules('required');

        $form->hidden('token');
        $form->hidden('company_name');
        $form->saving(function ($form) {

            $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\\' | \`|\-|\=|\\\|\|/";
            $result = preg_match($regex,$form->user_name);

            if($result){
                $error = new MessageBag(['title'=>'提示','message'=>'用户名称请勿填写特殊字符!']);
                return back()->withInput()->with(compact('error'));
            }

            if($form->relator_phone !== $form->model()->relator_phone && User::where('relator_phone',$form->relator_phone)->value('id')){
                $error = new MessageBag(['title'=>'提示','message'=>'该用户已存在!']);
                return back()->withInput()->with(compact('error'));
            }

            $form->token = '';
            $company_info_name = Company::find($form->company_id);
            if($company_info_name){
                $form->company_name = $company_info_name->company_name;
            }

             // if ($form->password && $form->model()->password != $form->password) {
             //     $form->password = bcrypt($form->password);
             // }

        });

        $form->switch('user_status', '用户状态')->states($states);
        $case = [
            'on' => ['value' => 1, 'text' => '已认证', 'color' => 'primary'],
            'off'  => ['value' => 4, 'text' => '未认证', 'color' => 'default'],
        ];
        $form->switch('real_auth_status','是否通过实名认证')->states($case)->default('1');
        
        $case2 = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'primary'],
            'off'  => ['value' => 2, 'text' => '否', 'color' => 'default'],
        ];
        $form->switch('company_is_admin','是否是后台绑定公司')->states($case2)->default('1');

//         $form->saving(function (Form $form) {
//             //修改密码
//             $userService = new UserService(new User());
//             $userName = $form->relator_phone;
//             if(empty($userName)){
//                 //修改
//                 $targetUrl = config('app.cas_url_prefix').'modifyPassword';
//                 $user = User::find($form->model()->id);
//                 $updatePass = $userService->casUpdatePass($targetUrl, $user->relator_phone, $user->password, $form->password, $form->password, $user->token);
//             }
//         });
        
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
                    $data = array();
                    $data['type'] = 1;
                    $data['wx_openid'] = '';
                    $data['wx_info'] = '';
                    $casUser = $userService->casLogin($targetUrlLogin, $form->relator_phone, $form->password, $data);
                    $token = $casUser['result']['token'];
                }
                //更新用户token
                $userService->updateToken($form->model()->id, $token);
            }
            
            User::where(['id'=>$form->model()->id])->update(['password'=>'']);
        });

        $form->tools(function (Form\Tools $tools) {
            // 去掉删除按钮
            $tools->disableDelete();
            // 去掉查看按钮
            $tools->disableView();
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
        $form->display('relator_phone', '用户名(手机号)');
        $form->text('user_name', '客户姓名')->rules('required|max:10');
        // $form->password('password','密码')->rules('required|min:6|max:10');
        $form->text('identity_card', '身份证号')->rules('required|min:15|max:18');
        $form->select('company_id', '企业名称')->options(Company::where(['state'=>1])->orderBy('id','desc')->get()->pluck('company_name', 'id'))->rules('required');
        $form->select('user_level_id', '用户等级')->options(UserLevel::where(['status'=>1,'state'=>1])->get()->pluck('level_name', 'id'))->rules('required');
        $form->select('sales_id', '所属销售')->options(AdminRole::where(['admin_roles.slug'=>'sales'])
            ->select('admin_users.username as username', 'admin_users.id as id')
            ->join('admin_role_users','admin_roles.id','=','admin_role_users.role_id')
            ->join('admin_users','admin_role_users.user_id','=','admin_users.id')
            ->orderBy('admin_users.id', 'desc')
            ->get()->pluck('username', 'id'))->rules('required');
        $case = [
            'on' => ['value' => 1, 'text' => '已认证', 'color' => 'primary'],
            'off'  => ['value' => 4, 'text' => '未认证', 'color' => 'default'],
        ];
        $form->switch('real_auth_status','是否通过实名认证')->states($case)->default('1');

        $case2 = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'primary'],
            'off'  => ['value' => 2, 'text' => '否', 'color' => 'default'],
        ];
        $form->switch('company_is_admin','是否是后台绑定公司')->states($case2)->default('1');

        $form->tools(function (Form\Tools $tools) {
            // 去掉删除按钮
            $tools->disableDelete();
            // 去掉查看按钮
            $tools->disableView();
        });
        $form->saving(function (Form $form) {

            $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\\' | \`|\-|\=|\\\|\|/";
            $result = preg_match($regex,$form->user_name);
            if($result){
                $error = new MessageBag(['title'=>'提示','message'=>'用户名称请勿填写特殊字符!']);
                return back()->withInput()->with(compact('error'));
            }
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
        $form->text('relator_phone', '用户名(手机号)')->rules('required|regex:/^1\d{10}$/|unique:users');
        $form->text('user_name', '客户姓名')->rules('required|max:10');
        $form->password('password','密码')->rules('required|min:6|max:10');
        $form->text('identity_card', '身份证号')->rules('required|min:15|max:18');
        $form->select('company_id', '企业名称')->options(Company::where(['state'=>1])->orderBy('id','desc')->get()->pluck('name', 'id'))->rules('required');
        $form->select('user_level_id', '用户等级')->options(UserLevel::where(['status'=>1,'state'=>1])->get()->pluck('level_name', 'id'))->rules('required');
        $form->select('sales_id', '所属销售')->options(AdminRole::where(['admin_roles.slug'=>'sales'])
            ->select('admin_users.username as username', 'admin_users.id as id')
            ->join('admin_role_users','admin_roles.id','=','admin_role_users.role_id')
            ->join('admin_users','admin_role_users.user_id','=','admin_users.id')
            ->orderBy('admin_users.id', 'desc')
            ->get()->pluck('username', 'id'))->rules('required');
        $form->hidden('token');
        $form->hidden('company_name');

        $case = [
            'on' => ['value' => 1, 'text' => '已认证', 'color' => 'primary'],
            'off'  => ['value' => 4, 'text' => '未认证', 'color' => 'default'],
        ];
        $form->switch('real_auth_status','是否通过实名认证')->states($case)->default('1');

        $case2 = [
            'on' => ['value' => 1, 'text' => '是', 'color' => 'primary'],
            'off'  => ['value' => 2, 'text' => '否', 'color' => 'default'],
        ];
        $form->switch('company_is_admin','是否是后台绑定公司')->states($case2)->default('1');

        $form->saving(function (Form $form) {
            $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\\' | \`|\-|\=|\\\|\|/";
            $result = preg_match($regex,$form->user_name);
            if($result){
                $error = new MessageBag(['title'=>'提示','message'=>'用户名称请勿填写特殊字符!']);
                return back()->withInput()->with(compact('error'));
            }
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉删除按钮
            $tools->disableDelete();
            // 去掉查看按钮
            $tools->disableView();
        });

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
       Log::info('resetPass',['id'=>$id, 'targetUrl'=>$targetUrl]);
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

    /**
     * 个人实名认证审核
     * @param $id
     * @param Content $content
     * @return Content
     */
    public function check($id,Content $content)
    {
        $user = User::where(['id'=>$id])->first()->toArray();

        if(empty($user)){
            admin_error('提示','非法操作');
            return back();
        }
        $userRejectLog = userRejectLog::with('hasOneAdminUser')->where(['user_id'=>$id])->get()->toArray();


        return $content
            ->header('客户管理')
            ->description(trans('实名认证'))
            ->body(view('admin.charts.user-check',[
                'user'=>$user,
                'userRejectLog'=>$userRejectLog
            ]));
    }

    /**
     * 保存审核信息
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function saveCheck($id,Request $request)
    {
        $status    = $request->post('status',0);

        if($status == 2){ //驳回
            $data=[
                'user_id'     => $id,
                'reject_msg'     => $request->post('reject_msg',''),
                'admin_user_id'  => Admin::user()->id,
                'created_at'     => date('Y-m-d H:i:s'),
            ];
            DB::table('user_reject_log')->insert($data);
        }

        //修改认证状态
        DB::table('users')->where(['id'=>$id])->update(['real_auth_status'=>$status]);

        admin_toastr('保存成功', 'success');
        return redirect('/admin/users');

    }
   
}
