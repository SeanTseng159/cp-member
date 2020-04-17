<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapIpassRoutes();

        $this->mapApiV1Routes();

        $this->mapApiV2Routes();

        $this->mapApiV3Routes();

        $this->mapLineRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiV1Routes()
    {
        Route::prefix('api/v1')
             ->middleware('api')
             ->namespace('App\Http\Controllers\Api')
             ->group(base_path('routes/Api/V1.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiV2Routes()
    {
        Route::prefix('api/v2')
             ->middleware('api')
             ->namespace('App\Http\Controllers\Api')
             ->group(base_path('routes/Api/V2.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiV3Routes()
    {
        Route::prefix('api/v3')
             ->middleware('api')
             ->namespace('App\Http\Controllers\Api')
             ->group(base_path('routes/Api/V3.php'));
    }

    /**
     * Define the "ipass" routes for the application.
     *
     * These routes all receive session state, etc.
     *
     * @return void
     */
    protected function mapIpassRoutes()
    {
        Route::group([
            'middleware' => 'ipass',
            'prefix' => 'ipass',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/ipass.php');
        });
    }

    /**
     * Define the "line" routes for the application.
     *
     * These routes all receive session state, etc.
     *
     * @return void
     */
    protected function mapLineRoutes()
    {
        Route::group([
            'middleware' => 'line',
            'prefix' => 'line',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/line.php');
        });
    }
}
