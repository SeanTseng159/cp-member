<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Gift;
use App\Repositories\BaseRepository;


class GiftRepository extends BaseRepository
{
    private $limit = 20;
    
    
    public function __construct(Gift $model)
    {
        $this->model = $model;
    }
    
}
