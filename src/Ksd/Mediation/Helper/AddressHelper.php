<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/8
 * Time: 下午 1:57
 */

namespace Ksd\Mediation\Helper;


trait AddressHelper
{
    public function address($address)
    {
        $processAddress = $address;
        $zipCodeRegex = '/([0-9]{3,5})/';
        $addressRegex = [
            'city' => '/(..+[\x{7e23}|\x{5e02}])/uU',
            'area' => '/(..+[\x{5e02}|\x{9109}|\x{93ae}|\x{5340}|\x{6751}|\x{91cc}])/uU'
        ];

        $result = new \stdClass();
        $result->fulltext = $address;

        foreach ($addressRegex as $key => $regex) {
            $raw = $this->addressRegex($regex, $processAddress);
            if($key === 'city') {
                $zipCode = $this->addressRegex($zipCodeRegex, $raw['raw']);
                $result->zipCode = $zipCode['raw'];
                $raw['raw'] = $zipCode['address'];
            }

            $processAddress = $raw['address'];
            $result->$key = $raw['raw'];
        }
        $result->street = $processAddress;
        return $result;
    }

    public function addressRegex($regex, $processAddress)
    {
        $result = [
            'address' => $processAddress,
            'raw' => ''
        ];
        preg_match($regex, $processAddress , $data);
        if (count($data) > 0) {
            $result = [
                'address' => str_replace($data[0], '', $processAddress) ,
                'raw' => $data[0]
            ];
        }
        return $result;
    }
}