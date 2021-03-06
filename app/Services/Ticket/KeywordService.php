<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\KeywordRepository;

class KeywordService extends BaseService
{
    protected $keywordRepository;

    public function __construct(KeywordRepository $keywordRepository)
    {
        $this->keywordRepository = $keywordRepository;
    }

    /**
     * 依 關鍵字 找商品
     * @param string $keyword
     * @return mixed
     */
    public function getProductsByKeyword($keyword = '')
    {
        return $this->keywordRepository->getProductsByKeyword($keyword);
    }

    /**
     * 依 關鍵字 找餐車
     * @param string $keyword
     * @return mixed
     */
    public function getDiningCarsByKeyword($keyword = '')
    {
        return $this->keywordRepository->getDiningCarsByKeyword($keyword);
    }
}
