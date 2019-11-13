<?php

namespace App\Http\Controllers\Web\Line;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Line\MemberService as LineMemberService;
use App\Services\MemberService;
use App\Parameter\Line\MemberParameter;
use Log;

class MemberController extends Controller
{
    protected $service;
    protected $memberService;
    protected $platform = 'web';

    public function __construct(LineMemberService $service, MemberService $memberService)
    {
        $this->service = $service;
        $this->memberService = $memberService;
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
    public function callback(Request $request, $platform = 'web')
    {
      try {
        $this->platform = $platform;
        Log::info('=== line callback check ===');

        if(isset($request->query()['error'])) return $this->failureRedirect('E0001','無法取得使用者資訊');
        $code = $request->query()['code'];
        $state = $request->query()['state'];
        //取access_token
        $tokenInfo = $this->service->accessToken($code, $platform);

        if(!$tokenInfo->access_token) return $this->failureRedirect('無法取得使用者資訊');
        $user_profile = $this->service->getUserProfile($tokenInfo->access_token);

        if(!$tokenInfo->id_token) return $this->failureRedirect('無法取得使用者資訊');
        $payload = $this->service->getPayload($tokenInfo);
        if(!isset($payload->email)) return $this->failureRedirect('E0002','無法取得Email');

        $result = (new MemberParameter)->member($user_profile, $payload);

        return $this->successRedirect($request->query());
      }
      catch (\Exception $e) {
          Log::info('=== line 會員登入錯誤 ===');
          return $this->failureRedirect();
      }
    }

    private function failureRedirect($errorCode, $message)
    {
      $result = [
        'code' => $errorCode,
        'message' => $message
      ];

      return view('line.error', json_encode($result));
    }

    private function successRedirect($data)
    {
      $result = [
        'code' => '00000',
        'message' => $data
      ];

      return view('line.success', json_encode($result));
    }
}
