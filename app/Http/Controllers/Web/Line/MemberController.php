<?php

namespace App\Http\Controllers\Web\Line;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Line\MemberService as LineMemberService;
use App\Services\MemberService;
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

    public function __construct(LanguageService $langService, LineMemberService $service, MemberService $memberService)
    {
        $this->service = $service;
        $this->memberService = $memberService;

        $this->lang = $langService->getLang();
        $this->citypassUrl = env('CITY_PASS_WEB');
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
      // try {
        $this->platform = $platform;
        // Log::info('=== line callback check ===');

        if(isset($request->query()['error'])) return $this->failureRedirect($request->query());
        // return $this->successRedirect($request->query());
        $code = $request->query()['code'];
        $state = $request->query()['state'];
        //取access_token
        $access_token = $this->service->accessToken($code, $platform);

        // return redirect($this->citypassUrl . $this->lang);
        dd($access_token);
      // }
      // catch (\Exception $e) {
      //     // Log::info('=== ipass 會員登入錯誤 ===');
      //     return $this->failureRedirect();
      // }
    }

    private function failureRedirect()
    {
      return view('line.error');
    }

    private function successRedirect()
    {
      return view('line.success');
    }
}
