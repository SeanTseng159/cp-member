<?php
/**
 * User: lee
 * Date: 2018/03/15
 * Time: 上午 9:42
 */

namespace App\Traits;

trait ValidatorHelper
{
    /**
     * 驗證身分證
     * @param id $id
     * @return boolean
     */
    public function checkPid($cardid)
    {
        //先將字母數字存成陣列
        $alphabet =['A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14','F'=>'15','G'=>'16','H'=>'17','I'=>'34','J'=>'18','K'=>'19','L'=>'20','M'=>'21','N'=>'22','O'=>'35','P'=>'23','Q'=>'24','R'=>'25','S'=>'26','T'=>'27','U'=>'28','V'=>'29','W'=>'32','X'=>'30','Y'=>'31','Z'=>'33'];

        //檢查字元長度
        if (strlen($cardid) !=10) return false;

        //驗證英文字母正確性
        $alpha = substr($cardid,0,1); //英文字母
        $alpha = strtoupper($alpha); //若輸入英文字母為小寫則轉大寫
        if (!preg_match("/[A-Za-z]/",$alpha)) return false;

        //計算字母總和
        $nx = $alphabet[$alpha];
        $ns = $nx[0]+$nx[1]*9; //十位數+個位數x9

        //驗證男女性別
        $gender = substr($cardid,1,1); //取性別位置
        if ($gender !='1' && $gender !='2') return false;

        //N2x8+N3x7+N4x6+N5x5+N6x4+N7x3+N8x2+N9+N10
        $i = 8;
        $j = 1;
        $ms =0;
        //先算 N2x8 + N3x7 + N4x6 + N5x5 + N6x4 + N7x3 + N8x2
        while ($i >= 2){
            $mx = substr($cardid,$j,1);//由第j筆每次取一個數字
            $my = $mx * $i;//N*$i
            $ms = $ms + $my;//ms為加總
            $j+=1;
            $i--;
        }

        //最後再加上 N9 及 N10
        $ms = $ms + substr($cardid,8,1) + substr($cardid,9,1);
        //最後驗證除10
        $total = $ns + $ms;//上方的英文數字總和 + N2~N10總和
        if (($total%10) != 0) return false;

        return true;
    }

    /**
     * 驗證手機
     * @param country $country [國家代碼]
     * @param countryCode $countryCode [國碼]
     * @param cellphone $cellphone [號碼]
     * @return mixd
     */
    public function VerifyPhoneNumber($country, $countryCode, $cellphone)
    {
        if (!$country || !$countryCode || !$cellphone) return false;

        try {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($countryCode . $cellphone, strtoupper($country));

            $countryCode = $phoneNumber->getCountryCode();
            $cellphone = $phoneNumber->getNationalNumber();

            if (!$phoneUtil->isValidNumber($phoneNumber)) return false;

            if ($phoneUtil->getNumberType($phoneNumber) != 1 && $phoneUtil->getNumberType($phoneNumber) != 2) return false;
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }

        return [
            'country' => $country,
            'countryCode' => $countryCode,
            'cellphone' => $cellphone,
        ];
    }

    /**
     * 驗證地址
     * @param address $address
     * @return boolean
     */
    public function VerifyAddress($address)
    {
        return (preg_match("/([0-9]+[號号])|([nN][oO])/", $address));
    }

    /**
     * 驗證密碼
     * @param password $password
     * @return boolean
     */
    public function VerifyPassword($password)
    {
        if (!$password) return false;

        $result1 = preg_match("/^[a-zA-Z0-9]{8,16}$/", $password);
          // 先測試是否有英文
        $result2 = preg_match("/[a-zA-Z]{1,}/", $password);
          // 先測試是否有數字
        $result3 = preg_match("/[0-9]{1,}/", $password);

        return ($result1 && $result2 && $result3);
    }

    /**
     * 驗證Email
     * @param string $mail
     * @return boolean
     */
    public function verifyEmail($email)
    {
        if (!$email) return false;

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
