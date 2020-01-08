<?php


namespace App\Repositories;

use App\Models\AwardRecord;
use Carbon\Carbon;


class AwardRecordRepository extends BaseRepository
{
    private $limit = 20;
    protected $model;


    public function __construct(AwardRecord $model)
    {
        $this->model = $model;
    }

    /** 我的獎品清單
     * @param $type $type :1:可使用/2:已使用or過期
     * @param $memberId
     * @param $client
     * @param $clientId
     * @return
     */
    public function list($type, $memberId, $client, $clientId)
    {

        //獎品
        $result = $this->model
            ->byUser($memberId)
            ->when($client, function ($query) use ($client, $clientId) {
                $query->where('model_type', $client)
                    ->where('model_spec_id', $clientId);
            })
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
                    $query->where('award_status', "1")
                        ->where('award_skunk_status', 0);//銘謝惠顧
                })
            ->with('award')
            ->get();
        return $result;
    }

    public function find($id, $memberId)
    {
        return $this->model
            ->with(['award.supplier', 'award.image'])
            ->where('award_record_id', $id)
            ->where('user_id', $memberId)
            ->first();
    }

    public function availableAward($memberId)
    {
        //獎品
        $result = $this->model
            ->byUser($memberId)->whereNull('verified_at')
            ->whereHas('award', function ($query) {
                $now = Carbon::now();
                $query->where('award_validity_start_at', '<=', $now)->where('award_validity_end_at', '>', $now)->where('award_status', "1");
            })
            ->with('award')
            ->count();

        return $result;
    }

}
