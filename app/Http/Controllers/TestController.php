<?php
namespace App\Http\Controllers;

use App\Services\MailService;
use App\Models\Carts;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class TestController{
    
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    
    public function regex()
    {
        $member = new \stdClass();
        $member->email = 'sheng.chen@touchcity.tw';
        $member->name = 'test';
        $item1 = new \stdClass();
        $item1->name = 'test1';
        $item1->spec = null;
        $item1->price = 100;
        $item1->imageUrl = 'https://backend.citypass.tw/upload/product/214/0b1e033355f0b62610f4357458c48456_s.jpg';
        $item2 = new \stdClass();
        $item2->name = 'test2';
        $item2->spec = 'test2 spec';
        $item2->price = 200;
        $item2->imageUrl = 'https://store.citypass.tw/pub/media/catalog/product/cache/image/265x265/beff4985b56e3afdbeabfc89641a4582/y/0/y006745000001_2_1.jpg';
        
        $cartItems = [
            $item1,
            $item2,
        ];
        dd($this->mailService->sendNotEmptyCart($member, $cartItems));
    }
}
