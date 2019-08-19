<?php

namespace App\Parameter;

class DiningCarParameter
{

    public function noticInfo($data,$body)
    {
        $params['dining_car_id'] = $data['prodId'];
        $params['notification_message'] = $body;
        $params['prod_type'] = $data['prodType'];
        $params['prod_id'] = $data['prodId'];
        return $params;
    }
}
