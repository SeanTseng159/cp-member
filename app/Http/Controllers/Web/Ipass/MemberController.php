<?php
/**
 * User: Lee
 * Date: 2017/12/20
 * Time: 下午2:20
 */

namespace App\Http\Controllers\Web\Ipass;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ipass\MemberService as IpassMemberService;
use App\Services\MemberService;
use Ksd\Mediation\Services\LanguageService;
use App\Parameter\Ipass\MemberParameter;
use Log;

class MemberController extends Controller
{
    protected $service;
    protected $memberService;
    protected $platform = 'web';
    protected $citypassUrl;
    protected $lang;

    const OPEN_PLATEFORM = 'ipass';

    public function __construct(LanguageService $langService, IpassMemberService $service, MemberService $memberService)
    {
        $this->service = $service;
        $this->memberService = $memberService;

        $this->lang = $langService->getLang();
        $this->citypassUrl = env('CITY_PASS_WEB');
    }

    /**
     * 登入
     * @param Illuminate\Http\Request $request
     */
    public function login(Request $request, $platform = 'web')
    {
        $parameter = (new MemberParameter)->authorize();
        $auth = $this->service->authorize($parameter);

        try {
            if ($auth->statusCode !== 200) return ($platform === 'app') ? 'app://ipassLogin?result=false' : $this->citypassUrl . $this->lang . '/oauth/failure';
            $data = (array) $auth->data;
            $data['redirect_url'] = ($platform === 'app') ? url('ipass/memberCallback/app') : url('ipass/memberCallback');
            $data['cancel_url'] = ($platform === 'app') ? 'app://ipassLogin?result=false' : $this->citypassUrl . $this->lang . '/oauth/failure';
            return view('ipass.login', $data);
        }
        catch (\Exception $e) {
            return $this->failureRedirect();
        }
    }

    /**
     * 登入資訊
     * @param Illuminate\Http\Request $request
     */
    public function callback(Request $request, $platform = 'web')
    {
        $this->platform = $platform;
        Log::info('=== ipass callback check ===');

        $memberParameter = new MemberParameter;
        $parameter = $memberParameter->callback($request);
        $ipassMember = $this->service->member($parameter);

        Log::info('=== ipass 登入 ===');

        try {
            if ($ipassMember->statusCode !== 200) return redirect('ipass/login/' . $this->platform);
            $memberData = $ipassMember->data;

            Log::info('=== ipass 會員資料 ===');
            Log::debug(print_r($memberData, true));

            // 檢查openId是否存在 (已註冊)
            $member = $this->memberService->findByOpenId($memberData->email, self::OPEN_PLATEFORM);

            // 會員已註冊，登入會員
            if ($member && $member->status && $member->isRegistered) {
                $token = $this->memberService->generateOpenIdToken($member, $this->platform);

                Log::info('=== ipass 會員已註冊，登入會員 ===');
            }
            else {
                // 檢查手機是否已使用，未使用自動帶入
                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

                $phoneNumber = $phoneUtil->parse('886' . $memberData->m_tel, strtoupper('tw'));
                    
                $countryCode = $phoneNumber->getCountryCode();
                $cellphone = $phoneNumber->getNationalNumber();
                    
                $parameter = $memberParameter->member($memberData);
                if (!$this->memberService->checkPhoneIsUse('tw', $countryCode, $cellphone))
                {  
                    $parameter['country'] = 'tw';
                    $parameter['cellphone'] = $cellphone;
                    $parameter['countryCode'] = $countryCode;
                    $parameter['isValidPhone'] = 1;
                }

                // 檢查身份證是否使用，未使用自動帶入
                if ($this->memberService->checkSocialIdIsUse($memberData->idn)) {
                    unset($parameter['socialId']);
                }
                
                Log::info('=== ipass 會員註冊 ===');
                Log::debug(print_r($parameter, true));
                $member = $this->memberService->create($parameter);

                // $parameter = $memberParameter->member($memberData);
                // Log::info('=== ipass 會員註冊 ===');
                // Log::debug(print_r($parameter, true));
                // $member = $this->memberService->create($parameter);

                if (!$member) return $this->failureRedirect();

                $token = $this->memberService->generateOpenIdToken($member, $this->platform);

                Log::info('=== ipass 會員註冊成功 ===');
            }

            // 導登入頁
            return $this->successRedirect($token);
        }
        catch (\Exception $e) {
            Log::info('=== ipass 會員登入錯誤 ===');
            return $this->failureRedirect();
        }
    }

    private function successRedirect($token = '')
    {
        if ($this->platform === 'app') {
            $url = 'app://ipassLogin?result=true&token=' . $token;
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = $this->citypassUrl . $this->lang;
            $url .= '/oauth/success/' . $token;

            return redirect($url);
        }
    }

    private function failureRedirect()
    {
        if ($this->platform === 'app') {
            $url = 'app://ipassLogin?result=false';
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = $this->citypassUrl . $this->lang;
            $url .= '/oauth/failure';

            return redirect($url);
        }
    }

    /**
     * 登出
     * @param Illuminate\Http\Request $request
     */
    public function logout(Request $request, $platform = 'web')
    {
        $this->platform = $platform;
        $ipassMember = session('ipassMember');
        if ($ipassMember) {
            $result = $this->service->logout($ipassMember);

            Log::info('=== ipass 會員登出 ===');
            Log::debug(print_r(session()->getId(), true));
            Log::debug(print_r($result, true));
        }

        return '<script>location.href="' . $this->citypassUrl . '";</script>';
    }
}
