<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Services;

use Mail;
use Carbon;
use Crypt;
use Log;

class MailService
{
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

        $lang = 'zh_TW';

        $data['link'] = env('CITY_PASS_WEB') . $lang . '/validateEmail/' . $member->validEmailCode;

        return $this->send('歡迎使用 CityPass 都會通 - 註冊成功認證', $recipient, 'emails/register', $data);
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

        $lang = 'zh_TW';

        $data['link'] = env('CITY_PASS_WEB') . $lang . '/validateEmail/' . $member->validEmailCode;

        return $this->send('CityPass 都會通 - Email認證信', $recipient, 'emails/validateEmail', $data);
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

        $lang = 'zh_TW';

        $expires = Carbon\Carbon::now()->timestamp + 1800;
        $key = Crypt::encrypt($member->email . '_' . $expires);
        $data['link'] = env('CITY_PASS_WEB') . $lang . '/changePassword/' . $key;

        $data['name'] = $member->name;

        return $this->send('CityPass 都會通 - 密碼重設連結', $recipient, 'emails/forgetPassword', $data);
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
            'email' => env('MAIL_USERNAME', 'ksd0045ksd@gmail.com'),
            'name' => 'CityPass 都會通',
            'subject' => ($subject) ?: 'CityPass 都會通 - 通知信'
        ];

        $to = [
            'email' => $recipient['email'],
            'name' => (isset($recipient['name'])) ? $recipient['name'] : ''
        ];

        try {
            Mail::send($view, $viewData, function ($message) use ($from, $to) {
                $message->from($from['email'], $from['name']);
                $message->to($to['email'], $to['name'])->subject($from['subject']);
            });

            return true;
        } catch(\Exception $e) {
            Log::error('smtp 有問題, 請檢查!');
            return false;
        }
    }
}
