<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 03:00
 */

namespace Ksd\Mediation\Magento;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Ksd\Mediation\Result\OrderResult;
use App\Models\Member;

use Ksd\Mediation\Magento\Customer;
use Ksd\Mediation\Magento\Cart;

use Carbon\Carbon;

class Order extends Client
{
    private $member;
    private $magentoCustomer;
    private $cart;

    public function __construct()
    {
        $this->member = new Member();
        $this->magentoCustomer = new Customer();
        $this->cart = new Cart();
        parent::__construct();
    }


    /**
     * 取得所有訂單列表\
     * @param $email
     * @return array
     */
    public function info($email, $isNew = false)
    {
        $data = [];

        if(!empty($email)) {
            try {
                $today = Carbon::today();
                $startDate = $today->addDays(1)->format('Y-m-d');
                $endDate = $today->subMonths(3)->format('Y-m-d'); // 往後推三個月

                $path = 'V1/orders';

                $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                    ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email)
                    ->putQuery('searchCriteria[filterGroups][1][filters][1][field]', 'status')
                    ->putQuery('searchCriteria[filterGroups][1][filters][1][value]', 'canceled')
                    ->putQuery('searchCriteria[filterGroups][1][filters][1][condition_type]', 'neq')
                    ->putQuery('searchCriteria[filterGroups][2][filters][0][field]', 'created_at')
                    ->putQuery('searchCriteria[filterGroups][2][filters][0][value]', $endDate)
                    ->putQuery('searchCriteria[filterGroups][2][filters][0][condition_type]', 'from')
                    ->putQuery('searchCriteria[filterGroups][3][filters][0][field]', 'created_at')
                    ->putQuery('searchCriteria[filterGroups][3][filters][0][value]', $startDate)
                    ->putQuery('searchCriteria[filterGroups][3][filters][0][condition_type]', 'to')
                    ->request('GET', $path);
                $body = $response->getBody();
                $result = json_decode($body, true);

                if (!empty($result['items'])) {
                    foreach ($result['items'] as $item) {
                        if (isset($item['status'])) {
                            $order = new OrderResult();
                            if ($isNew) $order->newMagento($item);
                            else $order->magento($item);

                            $data[] = (array) $order;
                        }
                    }
                }
            } catch (ClientException $e) {
                // TODO:抓不到MAGENTO API訂單資料
            } catch (\Exception $e) {
                // TODO:抓不到MAGENTO API訂單資料
            }
        }

        return $data;
    }

    /**
     * 取得所有待付款訂單列表
     * @param $email
     * @return array
     */
    public function pendingOrders()
    {
        $data = null;

        try {
            $path = 'V1/orders';

            $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'status')
            ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', 'pending')
            ->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);

            if (!empty($result['items'])) {
                foreach ($result['items'] as $item) {
                    if (isset($item['status'])) { //訂單狀態為canceled不顯示
                        $order = new OrderResult();
                        $order->magento($item);
                        $data[] = $order;
                    }
                }
            }
        } catch (ClientException $e) {
            // TODO:抓不到MAGENTO API訂單資料
        }

        return $data;
    }


    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $parameter
     * @return array
     */
    public function order($parameter)
    {

        $itemId = $parameter->itemId;
        $id = $parameter->id;
        $email = $this->getEmail();
        $admintoken = new Client();
        $this->authorization($admintoken->token);
        $response =[];
        try{
            $path = 'V1/orders';
            $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email)
                ->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'increment_id')
                ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', $itemId)
                ->request('GET', $path);
        }catch (ClientException $e){
            // TODO:抓不到訂單資料
        }

        $body = $response->getBody();

        $result = json_decode($body, true);

        $data = [] ;
        if(!empty($result['items'][0])) {
            $order = new OrderResult();
            $order->magento($result['items'][0], true);
            $data[] = $order;
        }

        //如有關鍵字搜尋則進行判斷是否有相似字
        if(!empty($id)){
                $count = 0;
                foreach ($order->items as $items) {
                    if(!preg_match("/".$id."/",$items['id'])){
                        array_splice($order->items,$count,1);
                        $count--;
                    }
                    $count++;
                }
            $data[] = $order;
        }

        return $data;

    }


    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @param $email
     * @return array
     */
    public function search($parameters, $email)
    {

        if(!empty($email)) {
            $status = $this->orderStatusToMagento($parameters->status);
            $orderData = $parameters->orderData;
            $initDate = $parameters->initDate;
            $endDate = date("Y-m-d",strtotime($parameters->endDate."+1 day"));

            $orderItemResult = $this->searchItem($parameters);
            $response =[];
            try{
                $path = 'V1/orders';
                $this->clear();

                $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                    ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email);

                if(!empty($status)){
                    if ($status === 'refund') {
                        $this->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'status')
                            ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', 'holded,closed')
                        ->putQuery('searchCriteria[filterGroups][1][filters][0][condition_type]', 'in');
                    }
                    else {
                        $this->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'status')
                            ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', $status);
                    }

                }
                if(!empty($orderData)){
                    $ids = [];
                    foreach ($orderItemResult['items'] as $item ) {
                        $ids[] = $this->arrayDefault($item, 'order_id', 0);
                    }

                    $this->putQuery('searchCriteria[filterGroups][2][filters][0][field]', 'entity_id')
                        ->putQuery('searchCriteria[filterGroups][2][filters][0][value]', join(',', $ids))
                        ->putQuery('searchCriteria[filterGroups][2][filters][0][condition_type]', 'in')
                        ->putQuery('searchCriteria[filterGroups][2][filters][1][field]', 'increment_id')
                        ->putQuery('searchCriteria[filterGroups][2][filters][1][value]', '%'.$orderData.'%')
                        ->putQuery('searchCriteria[filterGroups][2][filters][1][condition_type]', 'like');
                }
                if(!empty($initDate)&&!empty($endDate)) {
                   $this->putQuery('searchCriteria[filterGroups][3][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][value]', $initDate)
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][condition_type]', 'from')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $endDate)
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'to');
                }
                if(!empty($initDate)&&empty($endDate)) {
                    $this->putQuery('searchCriteria[filterGroups][3][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][value]', $initDate)
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][condition_type]', 'from')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $endDate)
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'to');
                }
                if(empty($initDate)&&!empty($endDate)) {
                    $this->putQuery('searchCriteria[filterGroups][3][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][value]', $initDate)
                        ->putQuery('searchCriteria[filterGroups][3][filters][0][condition_type]', 'from')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'created_at')
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $endDate)
                        ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'to');
                }

                $response = $this->request('GET', $path);
                $body = $response->getBody();
                $data = [];
                $result = json_decode($body, true);
            }catch (ClientException $e){
                // TODO:抓不到訂單資料
                $result = [];
            }

            if (!$result) return $result;

            foreach ($result['items'] as $item) {
                if (isset($item['status']) && $item['status'] !== "canceled") { //訂單狀態為canceled不顯示
                    $order = new OrderResult();
                    $order->magento($item);
                    $data[] = (array)$order;
                }
            }

            //如有關鍵字搜尋則進行判斷是否有相似字
            $flag = false;
            $data1 = [];

            if(!empty($orderData) && !substr($orderData,0,1) ==='0'){
                foreach ($data as $item) {
                    $dataflag = false;
                    foreach ($item['items'] as $items) {
                        if(preg_match("/".$orderData."/",$items['name'])){
                            $flag = true;
                            $dataflag =true;
                        }
                    }
                    if($dataflag){
                        $data1[] = (array)$item;
                    }
                }
            } else {
                $flag = true;
                $data1 = (array)$data;
            }

            return $flag ? $data1 : [];
        } else {
            return [];
        }
    }


    /**
     * 取得訂單物流追蹤資訊
     * @param $order_id
     * @return string
     */
    public function getShippingInfo($order_id)
    {

        $path = 'V1/shipments';
        $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'order_id')
            ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $order_id)
            ->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        return $result['items'];

/*
        $data = null;
        if(!empty($result)) {
            foreach ($result['items'] as $items) {
                foreach ($items['items'] as $item) {
                    if (preg_match("/" . $sku . "/", $item['sku'])) {
                        $data = $items['tracks'];
                    }
                }
            }
            return $data;
        }else{

            return null;
        }
*/


    }
    /**
     * 根據 商品編號 取得圖片路徑
     * @param $sku
     * @return array
     */
    public function findItemImage($sku)
    {
        $path = "V1/products/$sku/media";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        return empty($result) ? ['file' => '', 'types' => [] ] : $result[0];
    }

    /**
     * 取得使用者email
     * @return string
     */
    public function getEmail()
    {
        $response = $this->request('GET', 'V1/customers/me');
        $result = json_decode($response->getBody(), true);
        $email = $result['email'];

        return $email;
    }

    /**
     * 根據訂單 id 查詢訂單資訊
     * @param $parameters
     * @param $useMergeResult [是否輸出整合後結果]
     * @return array
     */
    public function find($parameters, $useMergeResult = true, $isNew = false)
    {
        $id = $parameters->id;

        $path = sprintf('V1/orders/%s', $id);
        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if ($useMergeResult) {
            $data = [];
            $order = new OrderResult();
            if ($isNew) $order->newMagento($result, true);
            else $order->magento($result, true);
            $data[] = $order;

            return $data;
        }
        else {
            return $result;
        }
    }

    /**
     * 虛擬 ATM繳款紀錄回傳
     * @param $orderId
     * @return bool
     */
    public function writeoff($orderId)
    {

        $parameter = [
            'entity' => [
                'entity_id'=> $orderId,
                'status'=> 'processing'
            ]
        ];
        $this->putParameters($parameter);
        $response = $this->request('PUT', 'V1/orders/create');
        $result = json_decode($response->getBody(), true);

        return ($result['result'] === 'processing') ? true : false;
    }

    /**
     * 更新訂單
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {

        Log::debug('===iPassPay Update Order===');
        Log::debug($parameters->paySource);
        $id = isset($parameters->id) ? $parameters->id : $parameters->orderNo;
        $incrementId = $this->orderIdToIncrementId($id);
        //將ipasspay回傳結果存入order comment
        if ($parameters->paySource === 'ipasspay') {
            $dataArray = [
                'orderNo' => $parameters->orderNo,
                'order_id' => $parameters->order_id,
                'status' => $parameters->status,
                'txnseq' => $parameters->txnseq,
                'payment_type' => $parameters->payment_type,
                'amount' => $parameters->amount,
                'discount_amt' => $parameters->discount_amt,
                'redeem_amt' => $parameters->redeem_amt,
                'pay_amt' => $parameters->pay_amt,
                'pay_time' => $parameters->pay_time,
                'fund_time' => $parameters->fund_time,
                'respond_code' => $parameters->respond_code,
                'auth' => $parameters->auth,
                'card6no' => $parameters->card6no,
                'card4no' => $parameters->card4no,
                'eci' => $parameters->eci

            ];
            if(!empty($parameters->orderNo)) {
                $parameter = [
                    'statusHistory' => [
                        "comment" => implode('&', $dataArray)
                    ]

                ];

                $this->putParameters($parameter);
                $response = $this->request('POST', 'V1/orders/' . $id . '/comments');
                $result = json_decode($response->getBody(), true);
            }

            //依ipasspay回傳結果 更改訂單狀態 成功:processing ; 失敗:canceled

            if(isset($result) && $parameters->status === "Y"){
                $ipassParameter = [
                    'entity' => [
                        'entity_id' => $id,
                        'increment_id' => $incrementId,
                        'status' => 'processing',
                    ]
                ];
                $this->putParameters($ipassParameter);
                $response = $this->request('PUT', 'V1/orders/create');
                $result = json_decode($response->getBody(), true);
                return (isset($result)) ? true : false;
            }else{
                //三種ACCLINK、CREDIT、ECAC可重新付款，把原訂單變成canceled，並將原品項加回購物車，再重新結帳
                if($parameters->payment_type =='ACCLINK' || $parameters->payment_type =='CREDIT' || $parameters->payment_type =='ECAC'){
                    $this->getOrder($parameters->orderNo);

                    $ipassParameter = [
                        'entity' => [
                            'entity_id' => $id,
                            'increment_id' => $incrementId,
                            'status' => 'canceled',
                        ]
                    ];
                    $this->putParameters($ipassParameter);
                    $response = $this->request('PUT', 'V1/orders/create');
                    $result = json_decode($response->getBody(), true);
                    return (isset($result)) ? true : false;

                }else{// VACC、WEBATM、BARCODE，parameters->status === "N"，訂單狀態為pending(待付款)，故不做處理
                    $ipassParameter = [
                        'entity' => [
                            'entity_id' => $id,
                            'increment_id' => $incrementId,
                            'status' => 'pending',
                        ]
                    ];
                    $this->putParameters($ipassParameter);
                    $response = $this->request('PUT', 'V1/orders/create');
                    $result = json_decode($response->getBody(), true);
                    return (isset($result)) ? true : false;
                }

            }

        } else {
            $parameter = [
                'entity' => [
                    'entity_id' => $id,
                    'increment_id' => $incrementId,
                    'status' => 'processing',
                ]
            ];

            $this->putParameters($parameter);
            $response = $this->request('PUT', 'V1/orders/create');
            $result = json_decode($response->getBody(), true);
            return (isset($result)) ? true : false;
        }


    }

    /**
     * 根據訂單 id 把品項加回購物車(處理iPassPay重新付款)
     * @param $id
     * @return  bool
     */
    public function getOrder($id)
    {

        if(!empty($id)) {
            $path = sprintf('V1/orders/%s', $id);
            $response = $this->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);
            $result['status'];
            //
            if(isset($result['status']) && $result['status'] === "pending"){
                if(!empty($this->member->whereEmail($result['customer_email'])->first())){
                    $member = $this->member->whereEmail($result['customer_email'])->first();
                }else{
                    $email = explode("_",$result['customer_email']) ;
                    $member = $this->member->whereOpenid($email[1])->first();
                }

                if (isset($member)) {
                    $token = $this->magentoCustomer->token($member);
                    $this->cart->authorization($token)->createEmpty();
                    $cart = [];
                    foreach ($result['items'] as $items) {
                        $parameter = [
                            'id' => $items['sku'],
                            'source' => 'magento',
                            'quantity' => $items['qty_ordered'],
                            'additionals' => [],
                            'purchase' => [],

                        ];

                        array_push($cart, $parameter);
                    }
                    $this->cart->authorization($token)->add($cart);
                }
            }
            return true;
        }else{
            return false;
        }

    }

    /**
     * 更改訂單狀態
     * @param $id
     * @param $incrementId
     * @param $status
     * @return  bool
     */
    public function updateOrderState($id,$incrementId,$status)
    {
        if(!empty($id) && !empty($status)) {
            try {
                $parameter = [
                    'entity' => [
                        'entity_id' => $id,
                        'increment_id' => $incrementId,
                        'status' => $status,

                    ]
                ];
                $this->putParameters($parameter);
                $response = $this->request('PUT', 'V1/orders/create');
                $result = json_decode($response->getBody(), true);
            } catch (ClientException $e){

            }
        }
        return true;
    }

    /**
     * 搜尋訂單品項
     * @param $parameters
     * @return array|mixed
     */
    public function searchItem($parameters)
    {
        $orderData = $parameters->orderData;
        if (empty($orderData)) {
            return [];
        }

        try{
            $path = 'V1/orders/items';
            $this->putQuery('searchCriteria[filterGroups][2][filters][0][field]', 'name')
                ->putQuery('searchCriteria[filterGroups][2][filters][0][value]', '%'.$orderData.'%')
                ->putQuery('searchCriteria[filterGroups][2][filters][0][condition_type]', 'like');
            $response = $this->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);
            return $result;
        }catch (ClientException $e){
            // TODO:抓不到訂單資料
        }
        return [];
    }

    /**
     * 訂單ID轉換increment_id
     * @param $orderId
     * @return string
     */
    public function orderIdToIncrementId($orderId)
    {
        if(isset($orderId)) {
            $path = sprintf('V1/orders/%s', $orderId);
            $response = $this->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);

            return isset($result) ?  $result['increment_id'] :  null;
        }else{
            return null;
        }
    }

    /**
     * 訂單status轉換
     * @param $orderId
     * @return string
     */
    public function orderStatusToMagento($orderId)
    {
        if(!empty($orderId)) {
            switch ($orderId) {
                case '00': # 待付款
                    return "pending";
                case '01': # 已完成
                    return "complete";
                case '02': # 部分退貨
                    return "holded";
                case '03': # 已退貨
                    return "closed";
                case '04': # 處理中
                    return "holded";
                case '05': # 退貨 (退貨處理中 . 部分退貨 . 已退貨)
                    return "refund";
            }
        }else{
            return null;
        }

    }

    /**
     * 更改訂單狀態(ipassPay ATM結果回傳)
     * @param $parameters
     * @return  bool
     */
    public function updateIpassPayATM($parameters)
    {
        if(!empty($parameters->orderNo)){
            $id = $parameters->orderNo;
            $incrementId = $this->orderIdToIncrementId($id);

            $parameter = [
                'entity' => [
                    'entity_id' => $id,
                    'increment_id' => $incrementId,
                    'status' => 'processing',
                ]
            ];
            $this->putParameters($parameter);
            $response =$this->request('PUT', 'V1/orders/create');
            $result = json_decode($response->getBody(), true);

            return true;
        }else{
            return false;
        }
    }

}
