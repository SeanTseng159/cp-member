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
        if (!session('isViewLoginWeb')) return abort(404);

        $lang = 'zh-TW';

        return view('oauth::login', ['auth_client_id' => $id, 'web_url' => env('CITY_PASS_WEB') . $lang]);
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
        if (!session('isViewLoginWeb')) return abort(404);

        $oc = $this->ocService->find($id);

        return view('oauth::authorize', ['auth_client' => $oc]);
    }

    public function authorizeHandle(Request $request)
    {
        $auth_client_id = $request->input('auth_client_id');
        $revoked = $request->input('revoked');

        if ($revoked == 1) return $this->postFailure('E0001', '會員取消授權');

        $member = session('member');
        if (!$member) return $this->postFailure('E0021', '會員驗證失效');

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
        // 清除登入
        $request->session()->flush();
        \Log::debug(print_r(session('member'), true));

        $request->session()->regenerate();
        \Log::debug(print_r(session('member'), true));

        return $this->success(['code' => '00000', 'message' => 'success']);
    }
}
