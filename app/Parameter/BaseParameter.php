<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Parameter;

use App\Traits\MemberHelper;

class BaseParameter
{
    use MemberHelper;

    public $request;

    public $sort;
    public $limit;
    public $page;

    public $source;
    public $id;
    public $memberId;

    public function __construct($request)
    {
        $this->request = $request;

        $this->sort = $this->request->input('sort');
        $this->limit = $this->request->input('limit', 20);
        $this->page = $this->request->input('page', 1);

        $this->source = $this->request->input('source');
        $this->id = $this->request->input('id');
        $this->memberId = $this->getMemberId();
    }

    public function offset()
    {
        return $this->limit * $this->page;
    }
}
