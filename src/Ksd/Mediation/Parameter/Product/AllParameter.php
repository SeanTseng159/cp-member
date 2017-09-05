<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/31
 * Time: 下午 2:09
 */

namespace Ksd\Mediation\Parameter\Product;


use Ksd\Mediation\Parameter\BaseParameter;

class AllParameter extends BaseParameter
{
    public function codeigniterRequest($input)
    {
        parent::codeigniterRequest($input);
        $this->tags = $input->get('tags');
    }

    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->tags = $request->input('tags');
    }

    public function sort($a, $b)
    {
        if ($this->sort == 'created_at') {
            $format = "Y-m-d H:i:s";

            $now = \DateTime::createFromFormat($format, $a->createdAt);
            $before = \DateTime::createFromFormat($format, $b->createdAt);
            $compare = $now->getTimestamp() - $before->getTimestamp();

            if ($compare == 0) {
                return 0;
            }
            if ($this->direction == 'desc') {
                return $compare ? 1 : -1;
            }
            return $compare ? -1 : 1;
        } else if ($this->sort == 'price') {
            if ($a->price == $b->price) {
                return 0;
            }
            if ($this->direction == 'desc') {
                return $a->price < $b->price ? 1 : -1;
            }
            return $a->price < $b->price ? -1 : 1;
        }
    }
}