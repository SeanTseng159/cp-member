<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\FaqCategory;
use Carbon\Carbon;

class ServiceRepository extends BaseRepository
{

    public function __construct(FaqCategory $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 取常見問題
     * @return mixed
     */
    public function all($lang)
    {
        $date = Carbon::now()->toDateTimeString();
        return $this->missionModel->with(['contents' => function($query) use ($lang, $date) {
                                return $query->notDeleted()
                                            ->where('faq_content_status', 1)
                                            ->where('faq_content_lang', $lang)
                                            ->where('faq_content_release', '<=', $date)
                                            ->orderBy('faq_content_sort', 'asc')
                                            ->get();
                            }])
                            ->notDeleted()
                            ->where('faq_category_status', 1)
                            ->where('faq_category_lang', $lang)
                            ->orderBy('faq_category_sort', 'asc')
                            ->get();
    }
}
