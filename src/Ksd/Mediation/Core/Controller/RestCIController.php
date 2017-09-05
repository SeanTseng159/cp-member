<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/29
 * Time: 下午 2:25
 */

namespace Ksd\Mediation\Core\Controller;


class RestCIController extends REST_Controller
{
    public function success($data = NULL)
    {
        $this->response(
            $this->responseFormat($data),
            REST_Controller::HTTP_OK
        );
    }

    public function failure($code, $message, $data = [], $http_code = REST_Controller::HTTP_BAD_REQUEST)
    {
        $this->response([
            $this->responseFormat($data, $code, $message),
        ], $http_code);
    }

    public function responseFormat($data, $code = '00000', $message = 'success')
    {

        $this->output->set_header('Access-Control-Allow-Origin: *');
        $this->output->set_header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        $this->output->set_header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');

        $result = [
            'code' => $code,
            'message' => $message,
        ];
        if (!empty($data)) {
            $result['data'] = $data;
        }
        return $result;
    }

    public function requestJSON()
    {
        $json = $this->input->raw_input_stream;
        if (empty($json)) {
            return null;
        }
        return json_decode($json, true);
    }
}