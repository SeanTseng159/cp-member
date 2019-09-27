<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\Ticket;



class ShopBookingResult
{

    public function maxpeople($bookingLimit)
    {
        # code...
        $result = new \stdClass;
        $result->max=$bookingLimit->max_people;
        $result->precautions=$bookingLimit->precautions;
        return $result;
    }


}
