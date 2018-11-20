<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Goods;
use GuzzleHttp\Client;
use Maatwebsite\Excel;

/**
 * 商品导入自动处理
 */
class goodsUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goods:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'goods upload';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $excel_file_path = 
        \Excel::load($excel_file_path, function($reader) use( &$res ) {
            $reader = $reader->getSheet(0);
            $res = $reader->toArray();
            Log::info('excel_info',['res'=>$res]);

        });    
    }
}
