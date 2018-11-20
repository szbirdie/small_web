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
use App\Models\UserLevel;
use App\Admin\Extensions\SoftDelete;
use App\Services\UserLevelService;
use Illuminate\Support\MessageBag;

/**
 * 后台会员等级管理
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class UserLevelController extends Controller
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
            ->header('客户等级管理')
            // ->description(trans('会员等级管理'))
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
            ->header('查看客户等级')
            // ->description('查看')
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
            ->header('编辑客户等级')
            // ->description('编辑')
            ->body($this->form()->edit($id));
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
            ->header('新增客户等级')
            // ->description('创建')
            ->body($this->form());
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
        $grid = Admin::grid(UserLevel::class, function(Grid $grid){
            $grid->model()->orderBy('id','desc');
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 2, 'text' => '禁用', 'color' => 'default'],
            ];
            $grid->id('ID')->sortable();
            $grid->level_name('等级名称')->sortable();
            $grid->description('描述')->sortable();
            $grid->status('目前状态')->switch($states);
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                // prepend一个操作
                $actions->append(new SoftDelete($actions->getKey(),$actions->getResource()));
            });
            
        });
        $grid->disableExport();
        $grid->disableRowSelector(); 
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
        $show = new Show(UserLevel::findOrFail($id));
        $show->id('ID');
        $show->level_name('等级名称');
        $show->description('等级描述');
        $show->status('等级状态')->as(function ($status) {
            if($status==1){
                return '启用';
            }else{
                return '禁用';
            }
        });
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
        
        $form = new Form(new UserLevel());
        $id = $form->id;
        $form->display('id', 'ID');
        $form->text('level_name', '等级名称')->rules('required|regex:/[\p{Han}A-Za-z]/u',[
            'required'=>'等级名称必填',
            'regex'=>'格式有误',
            ]);
        $form->text('description', '等级描述');
        $form->switch('status', '目前状态')->states($states)->default(1);
        $form->saving(function (Form $form) {
            $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\\' | \`|\-|\=|\\\|\|/";
            $result = preg_match($regex,$form->level_name);
            if($result){
                $error = new MessageBag(['title'=>'提示','message'=>'等级名称请勿填写特殊字符!']);
                return back()->withInput()->with(compact('error'));
            }

            $form->description = $form->description ?? '';
            if(UserLevel::where('level_name',$form->level_name)->value('id') && $form->level_name !== $form->model()->level_name){
                $error = new MessageBag(['title'=>'提示','message'=>'该等级已存在!']);
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
        $userService = new UserLevelService(new UserLevel());
        $userService->del($id);
        return response()->json([
            'status'  => true,
            'message' => '删除成功 !',
        ]);
    }
   
}
