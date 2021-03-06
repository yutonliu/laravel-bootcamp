<?php

namespace Jetwaves\LaravelBootcamp\Providers;

use Illuminate\Support\ServiceProvider;
use Jetwaves\LaravelBootcamp\Commands\LaravelBootcampInitConsole;
use Route;


class LaravelBootcampServiceProvider extends ServiceProvider
{

    protected $commands = [
        'Jetwaves\LaravelBootcamp\Commands\LaravelBootcampInitConsole',
        'Jetwaves\LaravelBootcamp\Commands\LaravelBootcampGreetingsConsole',
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册当前包可以提供的 artisan命令
        $this->commands($this->commands);

        // 注册一个artisan命令别名，后续可以用   $this->commands('jetwaves.bootcamp.generate');  直接执行
        $this->app->singleton('jetwaves.bootcamp.generate', function () {
            return new LaravelBootcampInitConsole();
        });

        $this->app->singleton('jetwaves.bootcamp.inspire', function () {
            echo 'hello world';
        });

    }
}
