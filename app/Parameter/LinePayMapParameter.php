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
        $parameter['longitude'] = [
            (float)$request->input('leftlongitude', -180),
            (float)$request->input('rightlongitude', 180)
        ];
        $parameter['latitude'] = [
            (float)$request->input('rightlatitude', -90),
            (float)$request->input('leftlatitude', 90)
        ];
        return $parameter;
    }

}
