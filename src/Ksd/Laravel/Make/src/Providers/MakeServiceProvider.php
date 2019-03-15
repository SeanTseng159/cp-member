<?php
/**
 * User: Lee
 * Date: 2019/03/13
 * Time: 下午2:20
 */

namespace Ksd\Laravel\Make\Providers;

use Illuminate\Support\ServiceProvider;

use Ksd\Laravel\Make\Console\Commands\MakeModel;
use Ksd\Laravel\Make\Console\Commands\MakeService;
use Ksd\Laravel\Make\Console\Commands\MakeRepository;
use Ksd\Laravel\Make\Console\Commands\MakeMRS;

class MakeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModel::class,
                MakeService::class,
                MakeRepository::class,
                MakeMRS::class
            ]);
        }
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
