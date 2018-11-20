<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GoodsService;


/**
 * 指数定时同步
 */
class indexData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'index data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $goods;

    public function __construct()
    {
        $this->goods = new GoodsService();
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){


        $this->goods->index_data();


    }
}
