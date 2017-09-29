<?php

namespace App\Services;

use Mail;
use Carbon;
use Crypt;

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

        $data['link'] = 'http://172.104.83.229/validateEmail/' . $member->validEmailCode;

        $this->send('歡迎使用 CityPass 城市通 - 註冊成功認證', $recipient, 'emails/register', $data);
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

        $data['link'] = 'http://172.104.83.229/validateEmail/' . $member->validEmailCode;

        $this->send('CityPass 城市通 - Email認證信', $recipient, 'emails/validateEmail', $data);
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
        $data['link'] = 'http://172.104.83.229/changePassword/' . $key;

        $data['name'] = $member->name;

        $this->send('CityPass 城市通 - 密碼重設連結', $recipient, 'emails/forgetPassword', $data);
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
            'name' => 'CityPass 城市通',
            'subject' => ($subject) ?: 'CityPass 城市通通知信'
        ];

        $to = [
            'email' => $recipient['email'],
            'name' => (isset($recipient['name'])) ? $recipient['name'] : ''
        ];

        Mail::send($view, $viewData, function ($message) use ($from, $to) {
            $message->from($from['email'], $from['name']);
            $message->to($to['email'], $to['name'])->subject($from['subject']);
        });
    }

    /**
     * 非同步寄信 (未實作queue)
     *
     * @param string $subject
     * @param array $recipient
     * @param string $view
     * @param array $viewData
     * @return \App\Models\Member
     */
    public function queue($subject, $recipient, $view, $viewData = [])
    {
        $from = [
            'email' => env('MAIL_USERNAME', 'ksd0045ksd@gmail.com'),
            'name' => 'CityPass 城市通',
            'subject' => ($subject) ?: 'CityPass 城市通通知信'
        ];

        $to = [
            'email' => $recipient['email'],
            'name' => (isset($recipient['name'])) ? $recipient['name'] : ''
        ];

        Mail::queue($view, $viewData, function ($message) use ($from, $to) {
            $message->from($from['email'], $from['name']);
            $message->to($to['email'], $to['name'])->subject($from['subject']);
        });
    }
}
