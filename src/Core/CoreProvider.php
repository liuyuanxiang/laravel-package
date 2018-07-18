<?php

namespace Yashon\Laravel\Core;

use Illuminate\Support\ServiceProvider;

class CoreProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResources();

        //加载核心辅助函数
        require_once __DIR__ . '/Helper/Helper.php';

        //是否有设置代理,有则获取设置为代理
        if (!empty(config('app.proxy_ips'))) {
            Request()->setTrustedProxies(config('app.proxy_ips'));
        }

        //调试模式
        if (config('app.debug') === true) {
            //注册路由
            if (!$this->app->routesAreCached()) {
                require __DIR__ . '/routes/debug.php';
            }
            new \Yashon\Laravel\Core\Debuger();
        }

        //注册自动生成命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                'Yashon\Laravel\Core\Console\CreateModel',
                'Yashon\Laravel\Core\Console\CreateModelDoc',
                'Yashon\Laravel\Core\Console\CreateController',
                'Yashon\Laravel\Core\Console\CreateLogic',
                'Yashon\Laravel\Core\Console\CreateTest',
                'Yashon\Laravel\Core\Console\CreateSeeder',
                'Yashon\Laravel\Core\Console\CreateMigration',
                'Yashon\Laravel\Core\Console\CreateInit',

                'Yashon\Laravel\Core\Console\Config',
                'Yashon\Laravel\Core\Console\Supervisor'

            ]);
        }

    }

    /**
     * Register the Horizon resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'Yashon-package');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
