<?php


namespace App\Repositories;

use App\Models\AwardRecord;



class AwardRecordRepository extends BaseRepository
{
    private $limit = 20;
    private $model ;


    public function __construct(AwardRecord $model)
    {
        $this->model = $model;
    }

    /** 我的獎品清單
     * @param $type
     * @param $memberID
     */
    public function list($type,$memberID)
    {

    }




}
