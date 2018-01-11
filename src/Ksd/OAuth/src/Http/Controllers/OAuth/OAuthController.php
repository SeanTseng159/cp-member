<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Ksd\OAuth\Http\Controllers\BaseController;
use App\Services\MemberService;
use Ksd\OAuth\Services\OAuthClientMemberService;
use Ksd\OAuth\Services\OAuthClientService;

class OAuthController extends BaseController
{
    protected $service;
    protected $ocmService;
    protected $ocService;

    public function __construct(MemberService $service, OAuthClientMemberService $ocmService, OAuthClientService $ocService)
    {
        $this->service = $service;
        $this->ocmService = $ocmService;
        $this->ocService = $ocService;
    }

    public function login(Request $request, $id)
    {
        $platform = $request->input('platform');
        $platform = $platform ?: 'web';

        if (!session('isViewLoginWeb')) {
            $url = ($platform === 'app') ? 'app://ipassLogin?result=false' : env('IPASS_WEB_PATH') . '/oauth/city_pass?return_url=' . env('IPASS_WEB_PATH');
            return '<script>location.href="' . $url . '";</script>';
        }

        $lang = 'zh-TW';

        $path = ($platform === 'app') ? '/app' : '/member';
        $web_url = env('CITY_PASS_WEB') . $lang . $path;

        return view('oauth::login', ['auth_client_id' => $id, 'web_url' => $web_url]);
    }

    public function loginHandle(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $auth_client_id = $request->input('auth_client_id');

        $member = $this->service->findOnly($email, $password);

        if (!$member || $member->status == 0 || $member->isRegistered == 0) {
            return $this->postFailure('E0021', '會員驗證失效');
        }

        // save session
        $request->session()->put('member', $member);

        $checked = $this->ocmService->checkMemberAuthorize($auth_client_id, $member->id);
        if (!$checked) {
            return redirect('oauth/member/authorize/' . $auth_client_id);
        }

        return $this->postSuccess($this->ocmService->getResponseData($member));
    }

    public function authorize(Request $request, $id)
    {
        if (!session('isViewLoginWeb')) {
            $url = ($platform === 'app') ? 'app://ipassLogin?result=false' : env('IPASS_WEB_PATH') . '/oauth/city_pass?return_url=' . env('IPASS_WEB_PATH');
            return '<script>location.href="' . $url . '";</script>';
        }

        $oc = $this->ocService->find($id);

        return view('oauth::authorize', ['auth_client' => $oc]);
    }

    public function authorizeHandle(Request $request)
    {
        $auth_client_id = $request->input('auth_client_id');
        $revoked = $request->input('revoked');

        if ($revoked == 1) {
            // return $this->postFailure('E0001', '會員取消授權');
            $request->session()->forget('member');
            $cancel_url = session('cancel_url');
            $redirect_url = $cancel_url ?: env('IPASS_WEB_PATH');
            return '<script>location.href="' . $redirect_url . '";</script>';
        }

        $member = session('member');
        if (!$member) {
            // return $this->postFailure('E0021', '會員驗證失效');
            $cancel_url = session('cancel_url');
            $redirect_url = $cancel_url ?: env('IPASS_WEB_PATH');
            return '<script>location.href="' . $redirect_url . '";</script>';
        }

        $ocm = $this->ocmService->queryOne(['oauth_client_id' => $auth_client_id, 'member_id' => $member->id]);

        if ($ocm) {
            $this->ocmService->update($ocm->id, ['revoked' => $revoked]);
        }
        else {
            $this->ocmService->create([
                    'oauth_client_id' => $auth_client_id,
                    'member_id' => $member->id,
                    'revoked' => $revoked
                ]);
        }

        return $this->postSuccess($this->ocmService->getResponseData($member));
    }

    public function logout(Request $request)
    {
        $redirect_url = $request->input('redirect_url');
        // 清除登入
        $request->session()->forget('member');

        if ($redirect_url) {
            return '<script>location.href="' . $redirect_url . '";</script>';
        }
        else {
            exit();
        }
    }
}
