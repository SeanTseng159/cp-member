<?php

namespace App\Parameter;

use App\Parameter\BaseParameter;
use App\Config\Ticket\OrderConfig;
use App\Traits\MemberHelper;

class GreenPointParameter extends BaseParameter
{
    use MemberHelper;
    public function __construct($request)
    {
        parent::__construct($request);
    }

   public function params()
   {
       $memberID = $this->getMemberId();
       $data=new \stdClass();
       $data->memberId=$memberID;
       $data->device=10;
       $data->payment['gateway']=
       $data->payment['method']=
       $data->shipment['id']=
   }

   public function cart()
   {
       
   }
}
