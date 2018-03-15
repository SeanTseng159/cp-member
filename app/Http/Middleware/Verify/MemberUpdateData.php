<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;

class MemberUpdateData
{
    use ApiResponseHelper;
    use ValidatorHelper;

    private $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $data = $request->all();
        $id = $request->id;

        $member = $this->memberService->find($id);

        if (!$member) return $this->apiRespFailCode('E0061');

        // 確認身分證格式
        if ($data['isTw'] && $data['socialId']) {
            if (!$this->checkPid($data['socialId'])) return $this->apiRespFailCode('A0034');
        }
        // 確認身分證/護照是否使用
        if ($data['socialId'] && $data['socialId'] !== $member->socialId && $this->memberService->checkSocialIdIsUse($data['socialId'])) {
            return $this->apiRespFailCode('A0033');
        }
        
        return $next($request);
    }
}
