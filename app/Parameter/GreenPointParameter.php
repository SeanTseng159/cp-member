<?php

namespace App\Parameter;

use App\Traits\MemberHelper;
use Carbon\Carbon;

class GreenPointParameter 
{
    use MemberHelper;


   public function params()
   {
       $memberID = $this->getMemberId();
       $data=new \stdClass();
       $data->memberId=$memberID;
       $data->device=10;
       $data->payment['gateway']=10;
       $data->payment['method']=999;
       $data->shipment['id']=1;

       return $data;
   }

   public function cart($prod)
   {
        
        $cart=new \stdClass();
        $cart->totalQuantity=1;
        
        $cart->shippingFee=0;
        $cart->discountAmount=0;
        $cart->payAmount=0;
        $cart->type='citypass';
        $cart->quantity=1;        
       

        
       
        

        $type=new \stdClass();
        $type->id=$prod->prod_spec_price_id;
        $type->name=$prod->prod_spec_price_name;
        $type->useType=1;

        $spec=new \stdClass();
        $spec->id=$prod->prod_spec_id;
        $spec->name=$prod->prodSpec->prod_spec_name;


        $additional=new \stdClass();
        $additional->spec=$spec;
        $additional->type=$type;

        $item=new \stdClass();
        $item->additional=$additional;

        $item->quantity=1;
        $item->supplierId=$prod->prodSpec->productAll->supplier_id;
       
        $item->catalogId=0;
        $item->categoryId=0;
        $item->id=$prod->prodSpec->productAll->prod_id;
        $item->api=$prod->prodSpec->productAll->prod_api;
        $item->custId=$prod->prodSpec->productAll->prod_cust_id;
        
      
        
        $item->type=$prod->prodSpec->productAll->prod_type;
        $item->isPhysical=$prod->prodSpec->productAll->is_physical;
        $item->name=$prod->prodSpec->productAll->prod_name;
    
        
        $item->store=$prod->prodSpec->productAll->prod_store;
        $item->address=$prod->prodSpec->productAll->prod_zipcode.$prod->prodSpec->productAll->prod_county.$prod->prodSpec->productAll->prod_district.$prod->prodSpec->productAll->prod_address;
        $item->retailPrice=0;
        $item->price=0;
        $item->expireType=1;
        $item->expireStart=Carbon::today();
        $item->expireDue=Carbon::today()->adddays(180);;
        $item->groupExpireType='';
        
        $item->groups=false;
        $item->purchase=false;
        $items[]=$item;
        $cart->items=$items;
        
        
        return $cart;

   }
}
