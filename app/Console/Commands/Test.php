<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Logger;

use App\Services\Ticket\DiningCarMemberService;
use App\Services\FCMService;




class Test extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *

     * @var string
     */
    protected $signature = 'run:level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */


    public function handle(DiningCarMemberService $diningCarMemberService,FCMService $fCMService)
    {
        
        $data=$diningCarMemberService->findLevel(49, 1);
        //stkey為在哪個急遽
        //$totalStKey為有幾個極具

        $amount=$data->amount;
        $totalStKey=sizeof($data->diningCar->memberLevels)-1;
        if($totalStKey==0){$stkey=0;}
        else{
            foreach ($data->diningCar->memberLevels as $key=>$da ) 
            {
                //echo($da->limit);
                //檢查門檻值拿到該有的key
                if( $amount >= $da->limit )
                {
                    $stkey=$key;
                }
                else
                {
                    $stkey=$key-1;
                    break;
                }
 
            }
        }
        

        //加上剛剛的消費判斷，先判斷是否已經是頂級會員了!
        if($totalStKey>$stkey)
        {
            foreach (range($stkey,$totalStKey) as  $key) 
            {
                if($amount+8000 >= $data->diningCar->memberLevels[$key]->limit)
                {
                    $nekey=$key;
                }
                else
                {
                    $nekey=$key-1;
                    break;
                }
            }   

        }
        else
        {
            $nekey=$stkey;
        }

        //echo($data->diningCar->memberLevels);
        if($nekey>$stkey)
        {
            $memberId=array(49);
                    //echo($MCId);
            $pushData=array('prodType'  => 5,
                        'prodId' => 1,
                        'url' => [],
                        'name' => $data->diningCar->memberLevels[$nekey]->name );  
            $fCMService->memberNotify('diningCarMemberLevelUp',$memberId,$pushData);            

        }

        // echo($da->level);
        // echo($da->limit);
        // echo($da->status);
    }



}
