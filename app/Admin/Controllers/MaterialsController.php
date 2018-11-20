<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/11
 * Time: 14:28
 */

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Material;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Admin\Extensions\SoftDelete;
use Illuminate\Support\MessageBag;

class MaterialsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     * 搜索及列表页面
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('材质管理')
            ->description(trans('材质管理'))
            ->body($this->grid()->render());
    }

    /**
     * Edit interface.
     * 编辑页面
     * @param $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑')
            ->description('编辑')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     * 添加页面
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('添加材质')
            ->description('添加材质')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     * 列表方法
     * @return Grid
     */
    protected function grid()
    {
        $grid = Admin::grid(Material::class, function (Grid $grid) {
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                'off' => ['value' => 2, 'text' => '禁用', 'color' => 'danger'],
            ];
            $grid->model()->orderBy('id','desc');
            $grid->id('ID')->sortable();
            $grid->name('名称')->sortable();
            $grid->cate_id('关联品类')->display(function ($cate_id) {
                $category = Category::select('name')->where('id',$cate_id)->first();
                return $category->name;
            });
            $grid->description('描述')->sortable();
            $grid->state('状态')->switch($states);
            $grid->order('排序');

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                // 在这里添加字段过滤器
                $filter->like('name', '名称');
                $filter->equal('cate_id', '关联品类')->select(Category::where('depth',1)->where('state',1)->pluck('name', 'id'));
                $filter->equal('state', '状态')->select(array(" "=>"全部","1"=>"启用","2"=>"禁用"));
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
                // prepend一个操作
                $actions->append(new SoftDelete($actions->getKey(), $actions->getResource()));
            });

        });

        return $grid;
    }

    /**
     * 所有信息表单form.
     *
     * @return Form
     * @version 1.0.0
     * @author zhao
     */
    public function form()
    {
        return Admin::form(Material::class, function (Form $form) {
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                'off' => ['value' => 2, 'text' => '禁用', 'color' => 'danger'],
            ];
            $form->display('id', 'ID');
            $form->text('name', '名称')->rules(["required"], ['required' => '名称必填']);
            $form->select('cate_id', '关联品类')->options(Category::where('depth',1)->where('state',1)->pluck('name', 'id'))->default(1);
            $form->text('description', '描述');
            $form->switch('state','状态')->states($states)->default(1);
            $form->text('order','排序')->rules("required|regex:/^\d{1,10}$/", [
                'required' => '排序必填',
                'regex' => '排序格式错误'
            ])->default(0);
            $form->tools(function (Form\Tools $tools) {
                // 去掉删除按钮
                $tools->disableDelete();
                // 去掉查看按钮
                $tools->disableView();
            });
            $form->saving(function($form){
                if($form->name !== $form->model()->name && Material::where('name',$form->name)->where('cate_id',$form->cate_id)->value('id')){
                    $error = new MessageBag(['title'=>'提示','message'=>'该材质已存在!']);
                    return back()->withInput()->with(compact('error'));
                }
            });
        });
    }

    /**
     * 软删除
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function del($id){
        $res = Material::where('id',$id)->update(array('state'=>2));
        if($res){
            return response()->json([
                'status'  => true,
                'message' => '删除成功 !',
            ]);
        }else{
            return response()->json([
                'status'  => false,
                'message' => '删除失败 !',
            ]);
        }
    }

}