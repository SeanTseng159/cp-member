<?php


namespace App\Repositories;

use App\Models\AwardRecord;
use Carbon\Carbon;


class AwardRecordRepository extends BaseRepository
{
    private $limit = 20;
    private $model;


    public function __construct(AwardRecord $model)
    {
        $this->model = $model;
    }

    /** 我的獎品清單
     * @param $type $type :1:可使用/2:已使用or過期
     * @param $memberId
     * @return
     */
    public function list($type, $memberId)
    {

        //獎品
        $result = $this->model
            ->byUser($memberId)
            ->when($type,
                function ($query) use ($type) {
                    //獎品未使用
                    if ($type === 1) {
                        $query->whereNull('verified_at');
                    } //已使用或過期
                    else if ($type === 2) {
                        $query->whereNotNull('verified_at');
                    }
                })
            ->with(['award.supplier', 'award.image'])
            ->whereHas('award',
                function ($query) use ($type) {
                    $now = Carbon::now();
                    //獎品未使用
                    if ($type == 1) {
                        $query->where('award_validity_start_at', '<=', $now)->where('award_validity_end_at', '>', $now);
                    }
                    //已使用或過期
                    if ($type == 2) {
                        $query->where('award_validity_end_at', '>', $now);
                    }
                    $query->where('award_status', "1");
                })
            ->with('award')
            ->get();
        return $result;
    }

    public function find($id)
    {
        return $this->model
            ->with(['award.supplier', 'award.image'])
            ->where('award_record_id', $id)
            ->first();

    }
}
