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

        if(isset($request->query()['error'])) {
          Log::debug('=== line 無法取得使用者資訊 ===');
          return $this->failureRedirect();
        }
        $code = $request->query()['code'];
        $state = $request->query()['state'];

        //取tokenInfo
        $tokenInfo = $this->service->accessToken($code);
        if(!$tokenInfo->access_token || !$tokenInfo->id_token) {
          Log::debug('=== line 無法取得使用者資訊 ===');
          return $this->failureRedirect();
        }

        //取user_profile
        $user_profile = $this->service->getUserProfile($tokenInfo->access_token);

        //取payload
        $payload = $this->service->getPayload($tokenInfo->id_token);
        
        if(!isset($payload->nonce)) {
          Log::debug('=== line 無法取得裝置 ===');
          return $this->failureRedirect();
        }
        $this->platform = $payload->nonce;

        if(!isset($payload->email)) {
          Log::debug('=== line 無法取得Email ===');
          return $this->failureRedirect();
        }

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
          if (!$member) {
            Log::debug('=== line 會員註冊失敗 ===');
            return $this->failureRedirect();
          }

          //增加邀請碼並且寫入DB
          $inviteCode = $this->memberService->createInviteCode($member->id);

          $token = $this->memberService->generateOpenIdToken($member, $this->platform);
          Log::info('=== line 會員註冊成功 ===');
        }
        

        return $this->successRedirect($token);
      }
      catch (\Exception $e) {
          Log::info('=== line 會員登入錯誤 ===');
          Log::debug(print_r($e->getMessage(), true));
          return $this->failureRedirect();
      }
    }

    private function failureRedirect()
    {
        if ($this->platform === 'app') {
            $url = 'app://lineLogin?result=false';
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = $this->citypassUrl . $this->lang;
            $url .= '/oauth/failure';

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
