<?php

namespace App\Exceptions;

use App\Exceptions\Api\Checkout\PayCreditCardFailException;
use App\Traits\ApiResponseHelper;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    use ApiResponseHelper;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->isJson()) {
            return $this->JSONRender($request, $exception);
        }

        return parent::render($request, $exception);
    }

    public function JSONRender($request, Exception $exception)
    {
        if ($exception instanceof PayCreditCardFailException) {
            return $this->apiRespFail($exception->getCode(), $exception->getMessage());
        } else if($exception instanceof ValidationException) {
            $errors = $exception->validator->errors();
            return $this->apiRespFail('E0001','傳送參數錯誤', $errors);
        }
        return parent::render($request, $exception);
    }
}
