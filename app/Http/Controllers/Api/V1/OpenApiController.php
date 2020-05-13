<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use Ksd\Mediation\Parameter\Cart\CartParameter as OldCartParameter;
use Ksd\Mediation\Services\CartService as OldCartService;

// new
use App\Parameter\CartParameter;

use App\Services\Ticket\DiningCarService;


use App\Result\CartResult;

use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;


class OpenApiController extends RestLaravelController
{
    use CartHelper;


    protected $service;
    protected $result;

    public function __construct(DiningCarService $service)
    {
        $this->service = $service;
    }

    public function storeId(Request $request){


        try{
            $county=$request->input('City');
            $datas=$this->service->findByCounty($county);


            
            $postResult=[];
            foreach($datas as $data){
                if($data->name=='您的店車名稱'){

                }else{
                    $result=new \stdClass;
                    $result->id=str_pad($data->id,4,'0',STR_PAD_LEFT);
                    $result->Store_Name=$data->name;
                    $result->Categories=$data->category->name;
                    $result->Payment='LinePay';
                    $result->Coupon_Topic='消費滿千折百';
                    $result->Coupon_Info='憑LINE Pay結帳滿一千元送100 LINE POINTS';
                    $result->Link=env('CITY_PASS_WEB').(($data->type==1)?'diningCar/':'shop/').'detail/'.$data->id;
                    $result->Date='2020-12-30';
                    $result->City=$data->county;
                    $result->Address=$data->district.$data->address;
                    $postResult[]=$result;
                }
            }
            $status='200';
        }catch (Exception $e) {
            Log::error($e->getMessage());        
            if(empty($postResult)){
                $status='500';
                $message=$e->getMessage();
                $postResult=$this->error($message);    
            }
        }
        
        


        

        return response()->json($postResult, $status , [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-Requested-With, Authorization, Content-Type, Accept'
        ]);

    }


    public function error($word)
    {
        $message=new \stdClass;
        $message->message=$word;
        $result=new \stdClass;
        $result->_error=$message;
        $result->_status='ERR';
        return $result;
       
    }

}
