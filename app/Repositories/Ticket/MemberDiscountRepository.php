<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;




use App\Repositories\BaseRepository;

use Carbon\Carbon;
use DB;
use App\Core\Logger;

use  App\Models\Ticket\MemberDiscount;

class MemberDiscountRepository extends BaseRepository
{
    private $model;

    public function __construct(MemberDiscount $model)
    {
        $this->model=$model;
        
    }

    public function listCanUsed($memberID)
    {
        return $this->model->with([ 'discountCode',
                                    'discountCodeBlock',
                                    'discountCodeTag',
                                    'discountCodeTag.tagProdId',
                                    'discountCodeMember'])
                            ->where('member_id',$memberID)
                            ->where('status',1)
                            ->where('used',0)
                            ->get();
    }


}
