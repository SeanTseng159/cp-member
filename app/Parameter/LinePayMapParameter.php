<?php

namespace App\Parameter;

class LinePayMapParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function stores($request)
    {
        $min_longitude = (float)$request->input('minlongitude', -180);
        $max_longitude = (float)$request->input('maxlongitude', 180);
        if ($min_longitude > $max_longitude) {
            $parameter['longitude'] = [$max_longitude, $min_longitude];
        } else {
            $parameter['longitude'] = [$min_longitude, $max_longitude];
        }
        
        $min_latitude = (float)$request->input('minlatitude', -90);
        $max_latitude = (float)$request->input('maxlatitude', 90);
        if ($min_latitude > $max_latitude) {
            $parameter['latitude'] = [$max_latitude, $min_latitude];
        } else {
            $parameter['latitude'] = [$min_latitude, $max_latitude];
        }
        
        return $parameter;
    }

}
