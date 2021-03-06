<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Services;

use Ksd\Mediation\Services\LanguageService;
use App\Services\DiscountCodeService;

use Mail;
use Carbon;
use Crypt;
use Log;

class MailService
{
    protected $lang;

    public function __construct(LanguageService $langService, DiscountCodeService $discountCodeService)
    {
        $this->lang = $langService->getLang();
        $this->discountCodeService = $discountCodeService;
        if (!$this->lang) $this->lang = env('APP_LANG');
    }

    /**
     * 註冊信
     * @param string $member
     * @return \App\Models\Member
     */
    public function sendRegisterMail($member)
    {
        $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];

        $data['email'] = $member->email;
        $data['isOauth'] = ($member->openPlateform !== 'citypass');
        $data['plateform'] = ($member->openPlateform === 'ipass') ? '愛PASS' : '';
        $data['link'] = env('CITY_PASS_WEB') . $this->lang . '/validateEmail/' . $member->validEmailCode;

        return $this->send('歡迎使用 CityPass都會通 - 註冊成功認證', $recipient, 'emails/register', $data);
    }

    /**
     * 註冊完成
     * @param string $member
     * @return \App\Models\Member
     */
    public function sendRegisterCompleteMail($member)
    {

        if($member->openPlateform==='facebook' || $member->openPlateform==='google')
        {
            $recipient = [
                'email' => $member->openId,
                'name' => $member->name
            ];
            $data['email'] = $member->openId;
        }else
        {
            $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];
            $data['email'] = $member->email;
        }

        if ($member->openPlateform === 'ipass') $data['plateform'] = '愛PASS';
        else $data['plateform'] = ucfirst($member->openPlateform);

        // 檢查現在是否有首購活動
        $discountFirst = $this->discountCodeService->discountFirst();
        $data['name'] = $member->name;
        $data['codeValue'] = count($discountFirst)>0 ? $discountFirst[0]->discount_code_value:'';
        $data['codeName'] = count($discountFirst)>0 ? $discountFirst[0]->discount_code_name:'';
        $data['endTime'] = count($discountFirst)>0 ? $discountFirst[0]->discount_code_endtime:'';
        //如果有首購活動則寄送通知
        if(!empty($data['codeValue']))
        {
            return $this->send('專屬CityPass都會通新會員的福利來囉！', $recipient, 'emails/registerFirstDiscount', $data);
        }
    }

    /**
     * Email認證信
     * @param string $member
     * @return \App\Models\Member
     */
    public function sendValidateEmail($member)
    {
        $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];

        $data['link'] = env('CITY_PASS_WEB') . $this->lang . '/validateEmail/' . $member->validEmailCode;

        return $this->send('CityPass都會通 - Email認證信', $recipient, 'emails/validateEmail', $data);
    }

    /**
     * 忘記密碼信
     * @param string $member
     * @return \App\Models\Member
     */
    public function sendForgetPasswordMail($member)
    {
        $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];

        $expires = Carbon\Carbon::now()->timestamp + 1800;
        $key = Crypt::encrypt($member->email . '_' . $expires);
        $data['link'] = env('CITY_PASS_WEB') . $this->lang . '/changePassword/' . $key;

        $data['name'] = $member->name;

        return $this->send('CityPass都會通 - 密碼重設連結', $recipient, 'emails/forgetPassword', $data);
    }

    /**
     * 邀請好友獲得禮物信件
     * @param string $member
     * @param array $parameter
     * @return \App\Models\Member
     */
    public function findFriendInvitationMail($member,$parameter)
    {
        if($member->openPlateform==='facebook' || $member->openPlateform==='google')
        {
            $recipient = [
                'email' => $member->openId,
                'name' => $member->name
            ];
        }else
        {
            $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];
        }
        $data['name'] = $member->name;
        $data['friendName'] = $parameter['friendName'];
        $data['giftName'] = $parameter['giftName'];

        return $this->send('邀請好友註冊CityPass成功！', $recipient, 'emails/invitation', $data);
    }

    /**
     * 輸入邀請碼獲得禮物信件
     * @param string $member
     * @return \App\Models\Member
     */
    public function invitationInputMail($member,$parameter)
    {
        if($member->openPlateform==='facebook' || $member->openPlateform==='google')
        {
            $recipient = [
                'email' => $member->openId,
                'name' => $member->name
            ];
        }else
        {
            $recipient = [
                'email' => $member->email,
                'name' => $member->name
            ];
        }
        $data['name'] = $member->name;
        $data['giftName'] = $parameter['giftName'];

        return $this->send('感謝您註冊CityPass，送上好禮一份！ ', $recipient, 'emails/invitationInput', $data);
    }

    /**
     * 客服QA通知信
     * @param string $member
     * @param string $parameters
     */
    public function sendServiceEmail($member,$parameters)
    {
        $recipient = [
            'email' => $parameters->email,
            'name' => $parameters->name
        ];

        $data['name'] = $parameters->name;
        $data['questionType'] = $parameters->questionType;
        $data['questionContent'] = $parameters->questionContent;
        $data['date'] = date("Y-m-d H:i:s");
        $data['phone'] = $parameters->phone;

        $this->sendCityPass('【CityPass】客服追蹤通知信_' . date("YmdHi"), $recipient, 'emails/serviceEmail', $data);
    }

    /**
     * 同步寄信
     * @param string $subject
     * @param array $recipient
     * @param string $view
     * @param array $viewData
     * @return \App\Models\Member
     */
    public function send($subject, $recipient, $view, $viewData = [])
    {
        $from = [
            'address' => 'noreply@citypass.tw',
            'name' => 'CityPass都會通',
            'subject' => $subject ?: 'CityPass都會通 - 通知信'
        ];

        $to = [
            'email' => $recipient['email'],
            'name' => (isset($recipient['name'])) ? $recipient['name'] : ''
        ];

        try {
            Mail::send($view, $viewData, function ($message) use ($from, $to) {
                $message->from($from['address'], $from['name']);
                $message->sender($from['address'], $from['name']);
                $message->to($to['email'], $to['name'])->subject($from['subject']);
            });

            return true;
        } catch(\Exception $e) {
            Log::error('smtp 有問題, 請檢查!');
            Log::error($e);
            return false;
        }
    }

    /**
     * 同步寄信(客服信箱)
     * @param string $subject
     * @param array $recipient
     * @param string $view
     * @param array $viewData
     * @return \App\Models\Member
     */
    public function sendCityPass($subject, $recipient, $view, $viewData = [])
    {
        $from = [
            'address' => 'noreply@citypass.tw',
            'name' => 'CityPass都會通',
            'subject' => $subject ?: 'CityPass都會通 - 通知信'
        ];

        $to = [
            'email' => $recipient['email'],
            'name' => (isset($recipient['name'])) ? $recipient['name'] : ''
        ];

        $toCityPass = [
            'email' => 'service@citypass.tw',
            'name' => 'CityPass都會通'
        ];

        try {
            Mail::send($view, $viewData, function ($message) use ($from, $to, $toCityPass) {
                $message->from($from['address'], $from['name']);
                $message->sender($from['address'], $from['name']);
                $message->to($to['email'], $to['name'])->cc($toCityPass['email'], $toCityPass['name'])->subject($from['subject']);
            });

            return true;
        } catch(\Exception $e) {
            Log::error('smtp 有問題, 請檢查!');
            Log::error($e);
            return false;
        }
    }

    /**
     * 購物車商品轉入追蹤清單
     * @param object $member
     * @param array $cartItems
     * @return bool
     */
    public function sendCleanCart($member, $cartItems = null)
    {
        $recipient = [
            'email' => $member->email,
            'name' => $member->name,
        ];

        $data = [
            'name' => $member->name,
            'link' => env('CITY_PASS_WEB') . $this->lang . '/member/wishlist',
            'items' => $cartItems,
        ];
        Log::info($recipient, $data);

        return $this->send('CityPass都會通 - 商品移至收藏清單通知', $recipient, 'emails/expiredCart', $data);
    }

    /**
     * 提醒購物車內商品尚未結帳
     * @param object $member
     * @param array $cartItems
     * @return bool
     */
    public function sendNotEmptyCart($member, $cartItems = null)
    {
        $recipient = [
            'email' => $member->email,
            'name' => $member->name,
        ];

        $data = [
            'name' => $member->name,
            'link' => env('CITY_PASS_WEB') . $this->lang . '/cart',
            'items' => $cartItems,
        ];
        Log::info($recipient, $data);

        return $this->send('CityPass都會通 - 未結帳提醒通知，請您儘快完成結帳~', $recipient, 'emails/notEmptyCart', $data);
    }

    /**
     * 訂位提醒email
     * @param object $member
     * @param array $cartItems
     * @return bool
     */
    public function sendBookingFinishMail($member, $data = null)
    {

        if(is_null($member->email)){
            $email=$member->openId;
        }else{
            $email=$member->email;
        }
        $recipient = [
            'email' => $email,
            'name' => $member->name,
        ];

        $data = [
            'shopname' =>$data->shop->name,
            'number' => $data->booking->number,
            'name'    => $data->member->name,
            'date' => $data->booking->date,
            'time' => $data->booking->time,
            'people'=> $data->booking->people,
            'link' => env('CITY_PASS_WEB') .'booking/'. $data->booking->code,
        ];
        Log::info($recipient, $data);

        return $this->send('CityPass都會通 -感謝您使用CityPass預訂餐廳，您的訂位資訊如下~', $recipient, 'emails/sendBookingFinishMail', $data);
    }

}
