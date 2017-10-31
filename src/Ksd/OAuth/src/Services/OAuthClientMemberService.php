<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Services;

use Ksd\OAuth\Repositories\OAuthClientMemberRepository;
use App\Services\JWTTokenService;

class OAuthClientMemberService
{
    protected $repository;

    public function __construct(OAuthClientMemberRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function queryOne($data)
    {
        return $this->repository->queryOne($data);
    }

    public function checkMemberAuthorize($auth_client_id, $member_id)
    {
        $ocm = $this->repository->queryOne(['oauth_client_id' => $auth_client_id, 'member_id' => $member_id]);

        // 已授權確認
        if ($ocm && !$ocm->revoked) {
            return true;
        }

        return false;
    }

    public function getMemberData($member)
    {
        unset($member->id);
        unset($member->password);
        unset($member->isTw);
        unset($member->openPlateform);
        unset($member->openId);
        unset($member->isValidPhone);
        unset($member->validPhoneCode);
        unset($member->isValidEmail);
        unset($member->validEmailCode);
        unset($member->token);
        unset($member->memo);
        unset($member->status);
        unset($member->isRegistered);
        unset($member->modifier);
        unset($member->deleted_at);
        unset($member->created_at);
        unset($member->updated_at);

        return $member;
    }

    public function getResponseData($member)
    {
        $jwtTokenService = new JWTTokenService;
        $token = $jwtTokenService->generateToken($member, 'oauth');

        $data = new \stdClass;
        $data->access_token = $token;
        $data->expires_in = time() + 7200;
        $data->token_type = 'Bearer';
        $data->member = $this->getMemberData($member);

        return $data;
    }
}
