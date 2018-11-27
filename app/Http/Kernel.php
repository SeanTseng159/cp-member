<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CollectAiData::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'ipass' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:120,1',
            'bindings',
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\Api\TraceRequest::class,
        ],

        'oauth' => [
            \Illuminate\Session\Middleware\StartSession::class
        ],

        'ipasspay' => [
            \Illuminate\Session\Middleware\StartSession::class
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \App\Http\Middleware\ThrottleRequests::class,
        'admin.jwt' => \App\Http\Middleware\Api\AdminJWT::class,
        'auth.jwt' => \App\Http\Middleware\Api\AuthJWT::class,
        'cors' => \Barryvdh\Cors\HandleCors::class,

        'verify.member.login' => \App\Http\Middleware\Verify\MemberLogin::class,
        'verify.member.create' => \App\Http\Middleware\Verify\MemberCreate::class,
        'verify.member.update.data' => \App\Http\Middleware\Verify\MemberUpdateData::class,
        'verify.member.changePassword' => \App\Http\Middleware\Verify\ChangePassword::class,
        'verify.send.validPhoneCode' => \App\Http\Middleware\Verify\SendValidPhoneCode::class,

        'verify.product.search' => \App\Http\Middleware\Verify\Product\Search::class,

        'verify.checkout.shipment' => \App\Http\Middleware\Verify\Checkout\Shipment::class,
        'verify.checkout.buyNow' => \App\Http\Middleware\Verify\Checkout\BuyNow::class,
        'verify.checkout.payment' => \App\Http\Middleware\Verify\Checkout\Payment::class,
    ];
}
