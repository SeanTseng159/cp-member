<?php

namespace App\Http;

use App\Http\Middleware\Verify\Checkout\MenuPayment;
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
            'throttle:30,1',
            'bindings',
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\Api\TraceRequest::class,
        ],

        'oauth' => [
            \Illuminate\Session\Middleware\StartSession::class
        ],

        'ipasspay' => [
            \Illuminate\Session\Middleware\StartSession::class
        ],

        'line' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
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
        'guest.jwt' => \App\Http\Middleware\Api\GuestJWT::class,
        'member.guest.jwt' => \App\Http\Middleware\Api\MemberOrGuestJWT::class,
        'cors' => \Barryvdh\Cors\HandleCors::class,

        'verify.guest.login' => \App\Http\Middleware\Verify\GuestLogin::class,
        'verify.member.login' => \App\Http\Middleware\Verify\MemberLogin::class,
        'verify.member.create' => \App\Http\Middleware\Verify\MemberCreate::class,
        'verify.member.update.data' => \App\Http\Middleware\Verify\MemberUpdateData::class,
        'verify.member.registerInvite' => \App\Http\Middleware\Verify\MemberRegisterInvite::class,
        'verify.member.registerCheck' => \App\Http\Middleware\Verify\MemberRegisterCheck::class,
        'verify.member.registerCheck2' => \App\Http\Middleware\Verify\MemberRegisterCheck2::class,
        'verify.member.changePassword' => \App\Http\Middleware\Verify\ChangePassword::class,
        'verify.send.validPhoneCode' => \App\Http\Middleware\Verify\SendValidPhoneCode::class,

        'verify.product.search' => \App\Http\Middleware\Verify\Product\Search::class,

        'verify.cart.buyNow' => \App\Http\Middleware\Verify\Cart\BuyNow::class,
        'verify.cart.buyNow.market' => \App\Http\Middleware\Verify\Cart\Market::class,
        'verify.cart.buyNow.info' => \App\Http\Middleware\Verify\Cart\Info::class,

        'verify.diningCar.map' => \App\Http\Middleware\Verify\DiningCar\Map::class,

        'verify.checkout.shipment' => \App\Http\Middleware\Verify\Checkout\Shipment::class,
        'verify.checkout.shipmentForConfim' => \App\Http\Middleware\Verify\Checkout\shipmentForConfim::class,
        'verify.checkout.payment' => \App\Http\Middleware\Verify\Checkout\Payment::class,
        'verify.checkout.payment.menu' => MenuPayment::class,

        'verify.partner.join' => \App\Http\Middleware\Verify\PartnerJoin::class,

        'verify.guest.order.detail' => \App\Http\Middleware\Verify\Order\Guest\Detail::class,
        'verify.guest.order.search' => \App\Http\Middleware\Verify\Order\Guest\Search::class,
    ];
}
