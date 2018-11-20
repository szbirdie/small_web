<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Models\AdminConfig;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::info('time_info',['time'=>'start']);

         $schedule->command('order:release') //订单定时释放
                  ->everyTenMinutes();

         $schedule->command('order:receive') //订单定时收货
                  ->daily();

        $AdminConfig = AdminConfig::where(['name'=>'market_status'])->first();
       if (!empty($AdminConfig['description'])) {
            Log::info('admin_config',['info'=>'market_status']);
            $time = explode(',', $AdminConfig['description']);
            switch ($AdminConfig['value']) {
                case '1':
                    $schedule->call(function () {
                        AdminConfig::where(['name'=>'market_status'])->update(['value'=>2]);
                        Log::info('admin_config_value',['value'=>2]);
                        Cache::forget('market_status');
                    })->dailyAt($time[0]);
                    break;
                case '2':
                    $schedule->call(function () {
                        AdminConfig::where(['name'=>'market_status'])->update(['value'=>1]);
                        Log::info('admin_config_value',['value'=>1]);
                        Cache::forget('market_status');
                    })->dailyAt($time[1]);
                    break;
            }
        }

        $schedule->command('goods:synchronize') //商品同步ERP自动处理
                  ->daily();

        $schedule->command('index:data') //每天同步指数信息
        ->daily();



    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
