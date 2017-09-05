<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/31
 * Time: 下午 1:58
 */

namespace Ksd\Mediation\Parameter;


class BaseParameter
{
    public function codeigniterRequest($input)
    {
        $this->sort = $input->get('sort');
        $this->direction = $input->get('direction');
        $this->limit = $input->get('limit');
        $this->page = $input->get('page');
    }

    public function laravelRequest($request)
    {
        $this->sort = $request->input('sort');
        $this->direction = $request->input('direction');
        $this->limit = $request->input('limit');
        $this->page = $request->input('page');
    }

    public function offset()
    {
        return $this->limit * $this->page;
    }

    public function sort($a, $b) {}
}