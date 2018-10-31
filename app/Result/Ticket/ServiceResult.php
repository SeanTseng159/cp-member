<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Config\BaseConfig;
use App\Result\BaseResult;

class ServiceResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取常見問題
     * @param $data
     */
    public function faq($categories)
    {
        if (!$categories) return [];

        foreach ($categories as $category) {
            $newCategories[] = $this->getCategory($category);
        }

        return $newCategories;
    }

    /**
     * 取分類
     * @param $category
     */
    private function getCategory($category)
    {
        if (!$category) return null;

        $category = $category->toArray();

        $result = new \stdClass;
        $result->categoryId = (string) $this->arrayDefault($category, 'faq_category_id');
        $result->title = $this->arrayDefault($category, 'faq_category_name');
        $result->items = $this->transformContents($this->arrayDefault($category, 'contents'));

        return $result;
    }

    /**
     * 取內容
     * @param $data
     */
    public function transformContents($contents)
    {
        if (!$contents) return [];

        foreach ($contents as $content) {
            $items[] = $this->getContent($content);
        }

        return $items;
    }

    /**
     * 取內容
     * @param $data
     */
    public function getContent($content)
    {
        if (!$content) return [];

        $result = new \stdClass;
        $result->title = $this->arrayDefault($content, 'faq_content_title');
        $result->body = $this->arrayDefault($content, 'faq_content_body');
        $result->release = $this->arrayDefault($content, 'faq_content_release');

        return $result;
    }
}
