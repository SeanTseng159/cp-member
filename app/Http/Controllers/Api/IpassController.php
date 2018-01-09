<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\Ipass\MemberService as IpassMemberService;

class IpassController extends RestLaravelController
{
    private $service;

    public function __construct(IpassMemberService $service)
    {
        $this->service = $service;
    }

    /**
     * 登出
     * @param Illuminate\Http\Request $request
     */
    public function logout(Request $request, $platform = 'web')
    {
        $this->platform = $platform;
        $ipassMember = session('ipassMember');
        $result = $this->service->logout($ipassMember);

        \Log::info('=== ipass 會員登出 ===');
        \Log::debug(print_r($result, true));

        return $this->success();
    }
}
