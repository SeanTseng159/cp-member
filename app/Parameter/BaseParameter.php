<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Parameter;


class BaseParameter
{
    public $request;

    public $sort;
    public $limit;
    public $page;

    public $source;
    public $id;

    public function __construct($request)
    {
        $this->request = $request;

        $this->sort = $this->request->input('sort');
        $this->limit = $this->request->input('limit');
        $this->page = $this->request->input('page');

        $this->source = $this->request->input('source');
        $this->id = $this->request->input('id');
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function offset()
    {
        return $this->limit * $this->page;
    }
}
