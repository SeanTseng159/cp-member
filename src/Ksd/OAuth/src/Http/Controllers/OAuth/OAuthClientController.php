<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Ksd\OAuth\Http\Controllers\BaseController;
use Ksd\OAuth\Services\OAuthClientService;
use Ksd\OAuth\Services\OAuthClientMemberService;
use App\Services\JWTTokenService;
use Carbon\Carbon;
use Validator;
use Log;

class OAuthClientController extends BaseController
{
    protected $service;
    protected $ocmService;

    public function __construct(OAuthClientService $service, OAuthClientMemberService $ocmService)
    {
        $this->service = $service;
        $this->ocmService = $ocmService;
    }

    public function create(Request $request)
    {
        $key = $request->input('key');

        // 用key保護
        if ($key === '53890045') {
            $data = $request->only([
                'name',
                'scopes',
                'redirect'
            ]);

            $oc = $this->service->create($data);
            return $this->success($oc);
        }

        return $this->failure();
    }

    public function authorize(Request $request)
    {
        $data = $request->only([
                'grant_type',
                'client_id',
                'client_secret',
                'scopes',
                'redirect_url'
            ]);

        $oc = $this->service->authorize($data['client_id'], $data['client_secret']);

        if ($oc) {
            $new = new \stdClass;
            $new->uid = $oc->uid;
            $new->grant_type = $oc->grant_type;
            $new->scopes = $data['scopes'];

            if ($data['grant_type'] === 'auth_code') {
                $new->code = $oc->code;
                $new->response_type = 'code';
                $new->redirect = $data['redirect_url'];
                $new->expires_at = Carbon::createFromFormat('Y-m-d H:i:s', $oc->expires_at)->timestamp;
            }
            elseif ($data['grant_type'] === 'password') {
                $jwtTokenService = new JWTTokenService;

                $new->response_type = 'token';
                $new->token_type = 'Bearer';
                $new->access_token = $jwtTokenService->generateOAuthToken($oc->uid);
                $new->expires_at = time() + 86400;
            }

            return $this->success($new);
        }

        return $this->failure();
    }

    public function generateToken(Request $request)
    {
        $data = $request->only([
                'response_type',
                'client_id',
                'code',
                'redirect_url',
                'cancel_url'
            ]);

        $validator = Validator::make($data, [
            'response_type' => 'required',
            'client_id' => 'required',
            'code' => 'required',
            //'redirect_url' => (env('APP_ENV') === 'production') ? 'required|active_url' : 'required'
            'redirect_url' => 'required',
            'cancel_url' => 'required'
        ]);

        $request->session()->put('redirect_url', $data['redirect_url']);
        $request->session()->put('cancel_url', $data['cancel_url']);

        Log::info('=== oauth session ===');
        Log::debug(print_r(session()->getId(), true));

        if ($validator->fails() || $data['response_type'] !== 'code') return $this->postFailure('E0001', '參數錯誤');

        $oc = $this->service->queryOne([
                    'uid' => $data['client_id'],
                    'code' => $data['code']
                ]);

        if (!$oc) return $this->postFailure('E0001', '參數錯誤');

        $check = $this->service->checkExpire($oc->expires_at);
        if ($check) return $this->postFailure('E0022', 'Authorize Time Over Expires');

        // 設定 session，控制登入畫面瀏覽時間
        $request->session()->put('isViewLoginWeb', true);

        // 檢查是否登入
        $member = session('member');
        if (!$member) {
            return redirect('oauth/member/login/' . $oc->id);
        }

        // 檢查是否授權
        $checked = $this->ocmService->checkMemberAuthorize($oc->id, $member->id);
        if (!$checked) {
            return redirect('oauth/member/authorize/' . $oc->id);
        }

        return $this->postSuccess($this->ocmService->getResponseData($member));
    }
}
