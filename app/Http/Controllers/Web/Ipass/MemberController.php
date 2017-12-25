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
use App\Parameter\Ipass\MemberParameter;
use Log;

class MemberController extends Controller
{
    protected $service;
    protected $memberService;
    protected $platform = 'web';

    const OPEN_PLATEFORM = '1';

    public function __construct(IpassMemberService $service, MemberService $memberService)
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
        $parameter = (new MemberParameter)->authorize();
        $auth = $this->service->authorize($parameter);

        try {
            if ($auth->statusCode !== 200) return abort(404);
            $data = (array) $auth->data;
            $data['redirect_url'] = ($platform === 'app') ? url('ipass/memberCallback/app') : url('ipass/memberCallback');
            return view('ipass.login', $data);
        }
        catch (Exception $e) {
            return abort(404);
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

        $parameter = (new MemberParameter)->callback($request);
        $member = $this->service->member($parameter);

        Log::info('=== ipass 登入 ===');
        Log::debug(print_r($member, true));

        try {
            if ($member->statusCode !== 200) return $this->failureRedirect();
            $memberData = $member->data;

            // 檢查openId是否存在 (已註冊)
            $loginMember = $this->memberService->findByOpenId($memberData->email, self::OPEN_PLATEFORM);

            Log::info('=== ipass 檢查openId是否存在 ===');
            Log::debug(print_r($loginMember, true));

            // 會員已註冊，登入會員
            if ($loginMember && $loginMember->status && $loginMember->isRegistered) {
                $token = $this->memberService->generateOpenIdToken($loginMember, $this->platform);

                Log::info('=== ipass 會員已註冊，登入會員 ===');
            }
            else {
                // 檢查帳號是否一樣並合併
                /*$member = $this->memberService->findByEmail($memberData->email);

                if ($member && $member->isRegistered) {
                    // 帳號存在並已註冊citypass會員，做合併
                }
                else {
                    // 帳號存在但未完成註冊citypass會員 or 帳號不存在
                    $member = $this->memberService->create($parameter);
                    if (!$member) return $this->failureRedirect();
                }*/
                $parameter = (new MemberParameter)->member($memberData);
                Log::info('=== ipass 會員註冊 ===');
                Log::debug(print_r($parameter, true));
                $member = $this->memberService->create($parameter);

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
        $lang = 'zh_TW';

        if ($this->platform === 'app') {
            $url = 'app://ipassLogin?result=true&token=' . $token;
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
            $url .= $lang;
            $url .= '/oauth/success/' . $token;

            return redirect($url);
        }
    }

    private function failureRedirect()
    {
        $lang = 'zh_TW';

        if ($this->platform === 'app') {
            $url = 'app://ipassLogin?result=false';
            return '<script>location.href="' . $url . '";</script>';
        }
        else {
            $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
            $url .= $lang;
            $url .= '/oauth/failure';

            return redirect($url);
        }
    }
}
