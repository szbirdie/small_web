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
use App\Admin\Extensions\ResetPass;
use App\Admin\Extensions\SoftDelete;
use App\Models\Recommend ;
use App\Models\RecommendPosition;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * 后台管理banner管理
 * @author zdk 317583717@qq.com
 * @version 1.0.0
 */
class NavigationController extends Controller
{
    use HasResourceActions;
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('模块列表')
            ->body($this->grid());
    }

    /**
     * Create interface.
     * 添加页面
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建模块')
            ->description('创建模块')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = Admin::grid(Recommend::class, function(Grid $grid){
            $grid->model()->where(['position' => 'mould'])->orderBy('order','desc');
            $grid->thumb('缩略图')->image();
            $grid->name('模块名称');
            $grid->order('排序')->editable();
            $grid->created_at('创建时间')->sortable();

            $grid->filter(function($filter){
                
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                

                
            }); 
                       
            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();

                // $actions->append(new SoftDelete($actions->getKey(),$actions->getResource()));
            });
            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableRowSelector();
        });
        return $grid;
    } 


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Recommend());
        $form->display('id', 'ID');
        $form->text('name','标题')->rules('required');
        $form->image('thumb', '缩率图')->uniqueName();
        $form->text('url','链接')->rules('required');
        $form->text('order','排序')->default(0)->rules('regex:/^[0-9]*$/',['regex:排序格式错误']);
        $form->hidden('type');
        $form->hidden('position');
        $form->hidden('position_id');
        $form->hidden('params');
        $form->tools(function (Form\Tools $tools) {
            // 去掉删除按钮
            $tools->disableDelete();
            // 去掉查看按钮
            $tools->disableView();
        });
        $form->saving(function ($form) {
            if(!$form->order){
                $form->order = 0;
            }
            $form->position = 'mould';
            $data = RecommendPosition::where('name','mould')->first();
            $form->type = $data->type;
            $form->position_id = $data->id;
            $form->params = '';
            //清除首页缓存
            Cache::forget('index_info');
        });

        $form->saved(function (Form $form) {
            $thumb = $form->model()->thumb;
            $thumb_url = public_path().'/upload/'.$thumb;
            $id = $form->model()->id;
            $thumb = env('APP_URL').'/upload/'.$thumb;

            $http = new Client();
            $response = $http->post(config('app.img_url_prefix').'image.php', [
                'form_params' => [
                    'origin_url' => $thumb,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            if ($data['status'] == 1) {
                Recommend::where(['id'=>$id])->update(['thumb'=>$data['info']]);
                unlink($thumb_url);
            }
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
        $data = Recommend::where('id',$id)->first();
        $data->state = 2;
        $data->save();
        return response()->json([
            'status'  => true,
            'message' => '删除成功 !',
        ]);
   } 

    /**
     * 详情页面
     * @author lvqing@kuaigang.net
     * @param  [type]  $id      [description]
     * @param  Content $content [description]
     * @return [type]           [description]
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('查看')
            ->description('查看')
            ->body($this->detail($id));
    } 

    /**
     * 查看详情方法
     * @author lvqing@kuaigang.net
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    protected function detail($id)
    {
        $show = new Show(Recommend::findOrFail($id));
        $show->id('ID');
        $show->thumb('缩略图')->image();
        $show->text('url','链接');
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
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑模块')
            ->body($this->form()->edit($id));
    } 
}
