<?php namespace App\Services;

/**
 * Class    Lib UUID    全球唯一索引值
 *
 * @package App\Libraries
 */
class UUID
{
    protected $mNumber      = 1;
    protected $mUUID        = [];

    private $mComCreateGuid = null;

    public function __construct()
    {
        $this->mComCreateGuid = (function_exists('com_create_guid'))
            ? true
            : false;
    }

    public function setCreate($pNumber = 1)
    {
        if(!is_int((int)$pNumber) or $pNumber < 1) throw new \Exception("UUID 數量參數錯誤!!");

        $array  = [];
        for($i = 0; $i < $pNumber; $i++){
            if ($this->mComCreateGuid) {
                $array[] = com_create_guid();
            } else {
                mt_srand((double)microtime()*10000);    //optional for php 4.2.0 and up.
                $charId = strtoupper(md5(uniqid(rand(), true)));
                $hyphen = chr(45);  // "-"
                $uuid   = //chr(123)  // "{"
                    substr($charId, 0, 8).$hyphen
                    .substr($charId, 8, 4).$hyphen
                    .substr($charId,12, 4).$hyphen
                    .substr($charId,16, 4).$hyphen
                    .substr($charId,20,12);
                //.chr(125);      // "}"
                $array[] = $uuid;
            }
        }
        $this->mUUID = $array;

        return $this;
    }

    public function getToString()
    {
        return implode(",", $this->mUUID );
    }

    public function getToArray()
    {
        return $this->mUUID;
    }
}
