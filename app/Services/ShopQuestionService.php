<?php
/**
 * User: Annie
 * Date: 2019/09/24
 */

namespace App\Services;

use App\Core\Logger;
use App\Repositories\ShopQuestionRepository;
use App\Repositories\ShopWaitingRepository;
use Ksd\SMS\Services\EasyGoService;


class ShopQuestionService extends BaseService
{
    protected $repository;

    public function __construct(ShopQuestionRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * 取詳細
     * @param $shopId
     * @return mixed
     */
    public function get($shopId)
    {
        return $this->repository->get($shopId);
    }

    public function getQuestionDetail($shopId)
    {
        return $this->repository->getQuestionDetail($shopId);
    }
    public function checkAnswer($versionId,$answerAry)
    {
        return $this->repository->checkAnswer($versionId,$answerAry);
    }


    public function store($memberId,$date,$answerAry){
        return $this->repository->store($memberId,$date,$answerAry);
    }

}
