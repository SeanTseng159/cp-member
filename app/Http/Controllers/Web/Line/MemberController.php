<?php

namespace App\Http\Controllers\Web\Line;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Line\MemberService as LineMemberService;
use App\Services\MemberService;
use App\Parameter\Line\MemberParameter;
use Ksd\Mediation\Services\LanguageService;
use Log;

class MemberController extends Controller
{
    protected $service;
    protected $memberService;
    protected $platform = 'web';
    protected $citypassUrl;
    protected $lang;

    const OPEN_PLATEFORM = 'line';

    public function __construct(LineMemberService $service, MemberService $memberService, LanguageService $langService)
    {
        $this->service = $service;
        $this->memberService = $memberService;
        $this->citypassUrl = env('CITY_PASS_WEB');
        $this->lang = $langService->getLang();
    }

    /**
     * 登入
     * @param Illuminate\Http\Request $request
     */
    public function login(Request $request, $platform = 'web')
    {
      $url = $this->service->loginUrl($platform);

      return redirect($url);
    }

    /**
     * 登入資訊
     * @param Illuminate\Http\Request $request
     */
    public function callback(Request $request)
    {
      try {
        Log::info('=== line callback check ===');

        if(isset($request->query()['error'])) return $this->failureRedirect('無法取得使用者資訊');
        $code = $request->query()['code'];
        $state = $request->query()['state'];

        //取access_token
        $tokenInfo = $this->service->accessToken($code);
        if(!$tokenInfo->access_token) return $this->failureRedirect('無法取得使用者資訊');

        //取user_profile
        $user_profile = $this->service->getUserProfile($tokenInfo->access_token);
        if(!$tokenInfo->id_token) return $this->failureRedirect('無法取得使用者資訊');

        //取payload
        $payload = $this->service->getPayload($tokenInfo);
        if(!isset($payload->email)) return $this->failureRedirect('無法取得Email');
        if(!isset($payload->nonce)) return $this->failureRedirect('無法取得裝置');
        
        $this->platform = $payload->nonce;

        // 檢查openId是否存在 (已註冊)
        $member = $this->memberService->findByOpenId($payload->email, self::OPEN_PLATEFORM);

        // 會員已註冊，登入會員
        if ($member && $member->status && $member->isRegistered) {
          $token = $this->memberService->generateOpenIdToken($member, $this->platform);

          Log::info('=== line 會員已註冊，登入會員 ===');
        }
        else {
          $result = (new MemberParameter)->member($user_profile, $payload);

          Log::info('=== line 會員註冊 ===');
          Log::debug(print_r($result, true));

          $member = $this->memberService->create($result);
          if (!$member) return $this->failureRedirect('會員註冊失敗');

          //增加邀請碼並且寫入DB
          $inviteCode = $this->memberService->createInviteCode($member->id);

          $token = $this->memberService->generateOpenIdToken($member, $this->platform);
          Log::info('=== line 會員註冊成功 ===');
        }
        

        return $this->successRedirect($token);
      }
      catch (\Exception $e) {
          Log::info('=== line 會員登入錯誤 ===');
          return $this->failureRedirect($e);
      }
    }

    private function failureRedirect($msg = '')
    {
        if ($this->platform === 'app') {
            $url = 'app://lineLogin?result=false&msg=' . $msg;
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = $this->citypassUrl . $this->lang;
            $url .= '/oauth/failure?msg=' . $msg;

            return redirect($url);
        }
    }

    private function successRedirect($token = '')
    {
        if ($this->platform === 'app') {
            $url = 'app://lineLogin?result=true&token=' . $token;
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = $this->citypassUrl . $this->lang;
            $url .= '/oauth/success/' . $token;

            return redirect($url);
        }
    }
}
