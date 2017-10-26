<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\NewsletterService;
use Validator;

class NewsletterController extends RestLaravelController
{
    protected $service;

    public function __construct(NewsletterService $service)
    {
        $this->service = $service;
    }

    /**
     * 新增電子報名單
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewsletter(Request $request)
    {
        $email = $request->input('email');

        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        $result = $this->service->checkEmailIsUse($email);

        if ($result) {
            $newsletter = $this->service->findByEmail($email);
            $newsletter = $this->service->update($newsletter->id, [
                    'schedule' => 0,
                    'status' => 1
                ]);
        }
        else {
            $newsletter = $this->service->create(['email' => $email]);
        }

        return ($newsletter) ? $this->success() : $this->failure('E0002', '新增失敗');
    }
}
