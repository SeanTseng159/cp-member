<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 06:01
 */

namespace Ksd\Mediation\Repositories;

use App\Models\NotificationMobile;

use Illuminate\Database\QueryException;

class NotificationRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    public function register($parameter){
        try{
            $notimob = new NotificationMobile();

            $old_registed = $notimob->where([
                ['platform', '=', $parameter['platform']],
                ['mobile_token', '=', $parameter['token']]
            ])
            ->get();

            if($old_registed->count() == 0){

                    $notimob->mobile_token = $parameter['token'];
                    $notimob->platform = $parameter['platform'];
                    if(array_key_exists('memberId',$parameter)){
                        $notimob->member_id = $parameter['memberId'];
                    }
                    //$notimob->device_id = $parameter['deviceId'];
                    $notimob->save();
                return $notimob;
            }else{
                $update_record = $old_registed->first();
                if(array_key_exists('memberId',$parameter)){
                    $update_record->member_id = $parameter['memberId'];
                    $update_record->save();
                }

                return true;
            }

        }catch(QueryException $e){

            return false;
        }
    }

}