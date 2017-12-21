<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Providers;

use Illuminate\Support\ServiceProvider;

class IPassPayServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'ipass');
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
