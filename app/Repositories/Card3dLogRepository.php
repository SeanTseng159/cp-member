<?php

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\Card3dLog;

class Card3dLogRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Card3dLog;
    }

    /**
     * 新增Log
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $log = new Card3dLog;
            $log->errorCode = $data['ErrorCode'];
            $log->errorMessage = $data['ErrorMessage'];
            $log->eci = $data['ECI'];
            $log->cavv = $data['CAVV'];
            $log->xid = $data['XID'];
            $log->totalAmount = $data['totalAmount'];
            $log->platform = $data['platform'];
            $log->source = $data['source'];
            $log->save();
            return $log;
        } catch (QueryException $e) {
            return false;
        }
    }
}
