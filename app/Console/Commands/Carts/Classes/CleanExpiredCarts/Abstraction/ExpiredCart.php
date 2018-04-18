<?php

namespace App\Console\Commands\Carts\Classes\CleanExpiredCarts\Abstraction;

use App;

abstract class ExpiredCart
{
    protected $expiredDays;
    protected $source;
    protected $memberId;
    protected $token;
    protected $cartDetail;
    protected $cartItemIds;
    protected $productService;
    protected $mailService;
    protected $memberService;
    protected $memberTokenService;
    protected $cartService;

    public function __construct()
    {
        $this->setProductService(App::make('Ksd\Mediation\Services\ProductService'));
        $this->setMemberTokenService(App::make('Ksd\Mediation\Services\MemberTokenService'));
        $this->setMemberService(App::make('App\Services\MemberService'));
        $this->setMailService(App::make('App\Services\MailService'));
        $this->setCartService(App::make('Ksd\Mediation\Services\CartService'));
    }
    
    public function handle()
    {
        $this->token();
        $this->cart();
        $this->cartItemIds();
        $this->deleteExpiredCart();
        $this->validItems();
        $this->addWishlist();
        $this->sendMail();
    }
    
    public function token()
    {
        $this->setToken($this->memberTokenService->getUserTokenByMemberId($this->source, $this->memberId));
    }
    
    public function cart()
    {
        $params = new \stdClass();
        $params->source = $this->source;
        $this->setCartDetail($this->cartService->mine($params, $this->token));
    }
    
    public function deleteExpiredCart()
    {
        $this->cartService->deleteExpiredCart($this->source, $this->memberId, $this->cartItemIds);
    }
    
    public function sendMail()
    {
        $member = $this->memberService->find($this->memberId);
        $cartItems = array_slice($this->cartDetail->items, 0, 10);
        $this->mailService->sendCleanCart($member, $cartItems);
    }
    
    public function setCartItemIds($cartItemIds)
    {
        $this->cartItemIds = $cartItemIds;
    }
    
    public function setProductService($productService)
    {
        $this->productService = $productService;
    }
    
    public function setCartDetail($cartDetail) 
    {
        $this->cartDetail = $cartDetail;
    }
    
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function setMemberId($memberId) 
    {
        $this->memberId = $memberId;
    }
    
    public function setMemberService($memberService)
    {
        $this->memberService = $memberService;
    }
    
    public function setMemberTokenService($memberTokenService)
    {
        $this->memberTokenService = $memberTokenService;
    }
    
    public function setMailService($mailService)
    {
        $this->mailService = $mailService;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setSource($source) 
    {
        $this->source = $source;
    }
    
    public function setExpiredDays($expiredDays) 
    {
        $this->expiredDays = $expiredDays;
    }
    
}
