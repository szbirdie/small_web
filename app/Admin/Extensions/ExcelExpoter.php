<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/10
 * Time: 14:01
 */

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UserLevelGoods;

class ExcelExpoter extends AbstractExporter
{
    protected $head = [];
    protected $body = [];
    protected $array = [];
    protected $filename = '';
    protected $sheetname = '';

    //设置属性
    public function setAttr($head, $body, $array, $filename, $sheetname = null){
        $this->head = $head;
        $this->body = $body;
        $this->array = $array;
        $this->filename = $filename;
        $this->sheetname = $sheetname;
    }

    public function export()
    {
        $data = UserLevelGoods::select('id','goods_weight','goods_id')->where('state','!=',2)->get()->toArray();

        if(!empty($this->array)){
            foreach($this->array as $n => $e_arr){
                if(isset($e_arr->goods_id)){
                    //计算剩余库存
                    $rweight = 0;
                    foreach ($data as $k){
                        if($k['goods_id'] == $e_arr->goods_id){
                            $rweight += $k['goods_weight'];
                        }
                    }
                    $this->array[$n]->inventory = bcsub($e_arr->weight,$rweight);
                    if($this->array[$n]->inventory <= 0){
                        $this->array[$n]->inventory = 0;
                    }
                }
            }
        }
        Excel::create($this->filename, function($excel) {
            $excel->sheet($this->sheetname, function($sheet) {
                // 这段逻辑是从表格数据中取出需要导出的字段
                $head = $this->head;
                $body = $this->body;
                $bodyRows = collect($this->getData())->map(function ($item)use($body) {
                    foreach ($body as $k => $keyName){
                        if(($keyName == 'weight') || ($keyName == 'goods_lock_weight') || ($keyName == 'goods_weight') || ($keyName == 'inventory')){
                            if($keyName == 'inventory'){
                                $inventory = 0;
                                foreach($this->array as $ar){
                                    if($ar->id == array_get($item, 'id')){
                                        if(isset($ar->inventory)){
                                            $inventory = $ar->inventory;
                                        }
                                    }
                                }
                                if($inventory){
                                    $arr[] = bcdiv($inventory,1000,2);
                                }else{
                                    $arr[] = bcdiv(array_get($item, $keyName),1000,2);
                                }
                            }else{
                                $arr[] = bcdiv(array_get($item, $keyName),1000,2);
                            }
                        }elseif(($keyName == 'cost') || ($keyName == 'price')){
                            $arr[] = bcdiv(array_get($item, $keyName),100,2);
                        }elseif($keyName == 'level_id'){
                            $arr[] = $_GET['user_level_id'] ?? '';
                        }elseif($keyName == 'type'){
                            if(array_get($item, $keyName) == 1){
                                $arr[] = '现货';
                            }elseif(array_get($item, $keyName) == 2){
                                $arr[] = '期货';
                            }else{
                                $arr[] = array_get($item, $keyName);
                            }
                        }else{
                            $arr[] = array_get($item, $keyName);
                        }
                    }
                    return $arr;
                });
                $rows = collect([$head])->merge($bodyRows);
                $sheet->rows($rows);
            });
        })->export('xlsx');
    }
}