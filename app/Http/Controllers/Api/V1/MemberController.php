<?php
/**
 * User: lee
 * Date: 2019/02/27
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MemberService;
use App\Services\NewsletterService;
use App\Services\JWTTokenService;
use App\Services\Ticket\MemberNoticService;
use App\Services\Ticket\InvitationService;
use App\Parameter\MemberParameter;
use App\Traits\CryptHelper;
use App\Traits\ValidatorHelper;
use App\Result\Ticket\MemberNoticResult;

use Hashids\Hashids;

class MemberController extends RestLaravelController
{
    use CryptHelper;
    use ValidatorHelper;

    protected $memberService;
    protected $lang;

  



    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
        
        $this->lang = env('APP_LANG');
    }

    /**
     * 餐車邀請 - 註冊會員
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerInvite(Request $request, NewsletterService $newsletterService)
    {
        try {
            $data = (new MemberParameter)->registerInvite($request);
            $type = $data['type'];

            $typeId = (new Hashids($type, 6))->decode($data['typeId']);
            unset($data['type']);
            unset($data['typeId']);

            $member = $this->memberService->createByInvite($data);

            if ($member) {
                $member = $this->memberService->generateToken($member, $data['device']);

                // 訂閱電子報
                $newsletter = $newsletterService->findByEmail($member->email);
                $newsletterData = [
                    'email' => $member->email,
                    'member_id' => $member->id
                ];

                ($newsletter) ? $newsletterService->update($newsletter->id, $newsletterData) : $newsletterService->create($newsletterData);

                // 發信
                $this->memberService->sendRegisterEmail($member);

                return $this->success([
                    'member' => [
                        'id' => $member->id,
                        'token' => $member->token,
                        'email' => $member->email,
                        'name' => $member->name,
                        'avatar' => $member->avatar,
                        'countryCode' => $member->countryCode,
                        'cellphone' => $member->cellphone,
                        'country' => $member->country,
                        'gender' => $member->gender,
                        'zipcode' => $member->zipcode,
                        'address' => $member->address,
                        'openPlateform' => $member->openPlateform
                    ],
                    'hashId' => $this->encryptHashId($type, $typeId[0])
                ]);
            }

            return $this->failureCode('E0012');
        } catch (Exception $e) {
            return $this->failureCode('E0012');
        }
    }

    /**
     *  檢查是否已註冊會員 [加密手機碼]
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCheck(Request $request)
    {
        try {
            $params = (new MemberParameter)->registerCheck($request);

            $phoneNumber = (new Hashids('PhoneNumber', 20))->decode($params['mobile']);
            if (!$phoneNumber) return $this->failureCode('E0301');

            // 確認手機格式
            $phoneNumber = $this->VerifyPhoneNumber($params['country'], $phoneNumber[0], $phoneNumber[1]);
            if (!$phoneNumber) return $this->failureCode('E0301');

            // 確認手機是否使用
            $member = $this->memberService->checkHasPhoneAndisRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone']);
            if ($member) {
                $isRegistered = true;
                $memberToken = (new Hashids('Member', 12))->encode([$member->id, time()]);
            }
            else {
                $isRegistered = false;
                $memberToken = '';
            }

            return $this->success([
                'isRegistered' => $isRegistered,
                'token' => $memberToken
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     *  檢查是否已註冊會員 [手機明碼]
     * @param Request $request
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCheck2(Request $request)
    {
        try {
            $phoneNumber = $request->phoneNumber;

            // 確認手機是否使用
            $member = $this->memberService->checkHasPhoneAndisRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone']);
            if ($member) {
                $isRegistered = true;
                $memberToken = (new Hashids('Member', 12))->encode([$member->id, time()]);
            }
            else {
                $isRegistered = false;
                $memberToken = '';
            }

            $phoneEncode = (new Hashids('PhoneNumber', 20))->encode([$phoneNumber['countryCode'], $phoneNumber['cellphone']]);

            return $this->success([
                'isRegistered' => $isRegistered,
                'token' => $memberToken,
                'mobile' => $phoneEncode
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    public function invitationInput(Request $request,InvitationService $invitationService)
    {
        try {
                $memberId = $request->memberId;
                $invitation = $request->invitation;
                //檢查邀請碼格式
                if (!preg_match("/^[A-Za-z0-9]{6}$/",$invitation)) return $this->failureCode('E0091');
                //判斷每個會員只能輸入一次邀請碼
                $invitationCheck = $invitationService->invitationCheck($memberId);
                if (!$invitationCheck) return $this->failureCode('E0094');
                //查詢被邀請碼會員
                $passiveMember = $this->memberService->invitationFind($invitation);
                if($passiveMember)
                {   
                    $passiveMemberId = $passiveMember->id;
                    $member = $this->memberService->find($memberId);
                    $gifts = $invitationService->allPromoteGift();
                    //會員無法輸入自己的邀請碼
                    if ($passiveMemberId === $memberId) return $this->failureCode('E0093');
                    //判斷是否還有禮物
                    if (count($gifts)==0) return $this->failureCode('E0078');
                    //新增送禮紀錄
                    $invitationService->addRecord($gifts,$memberId,$passiveMemberId);
                    //寄信
                    $parameter['friendName'] = $member->name;
                    foreach ($gifts as $key => $gift) {
                        switch ($gift->send_condition) {
                        //2:被邀請者 3:邀請者
                        case '2':
                             $passiveParameter['giftName'] = $gift->name;
                            $this->memberService->invitationInput($member,$passiveParameter);
                            break;
                        case '3':
                            $parameter['giftName'] = $gift->name;
                            $this->memberService->findFriendInvitation($passiveMember,$parameter);
                            break;
                        default:
                            # code...
                            break;
                        }
                    }
                    //寄信end
                    return $this->success();
        
                }else
                {
                    return $this->failureCode('E0090');
                }
            }catch (Exception $e) {
                    return $this->failureCode('E0007');
                }
        
            }

    public function info(Request $request,InvitationService $invitationService)
    {
        try{
            $memberId = $request->memberId;
            $member = $this->memberService->find($memberId);
            $friendValue = $invitationService->friendValue($memberId);
            if(empty($member->invited_code))return $this->failureCode('E0092');
            $url = env('CITY_PASS_WEB');
            $url .= $this->lang;

            //笨蛋式寫法
            $friendInviteApp = '<style="font-size:20px;"> <style="font-family:"MicrosoftJhengHeiRegular", "Microsoft JhengHei";">一次分享雙重獎勵 <style="font-size:14px;"> <style="font-family:"MicrosoftJhengHeiRegular", "Microsoft JhengHei";"><br> <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";">1. 透過您的Email、網路社群或通訊軟體轉貼專屬邀請碼邀請朋友加入 CityPass都會通 會員。 <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";"><br><br> <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";">2. 當您的好朋友註冊 CityPass都會通 時填入您的邀請碼，註冊成功後即可獲得 <style="font-family:"PingFangTC-Regular", "PingFang TC";color:#3FAEDE;"> iPhone抽獎一次 <style="font-family:"PingFangTC-Regular", "PingFang TC";">。您同時也可獲得 <style="font-family:"PingFangTC-Regular", "PingFang TC";color:#3FAEDE;"> iPhone抽獎一次 <style="font-family:"PingFangTC-Regular", "PingFang TC";">。 <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";"><br><br> <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";">3. 獎勵可累積，邀請好友成功次數越多抽獎次數也越多，得獎機會越大。 <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";"><br><br> <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";">4. 新註冊會員，註冊成功即可獲得 CityPass都會通  <style="font-family:"PingFangTC-Regular", "PingFang TC";color:#3FAEDE;">首次購物9折優惠。 <br><style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";">5. 活動時間： <style="font-family:"PingFangTC-Regular", "PingFang TC";color:#3FAEDE;">即日起至2019.12.31止。 <style="font-size:13px;"> <style="font-family:"PingFangTC-Regular", "PingFang TC";color:#3FAEDE;"><br>';




            //取的 PromoteGift 相關資訊
            $promoteGiftRow = $invitationService->findPromoteGift();
            //取的 ID排序最後的代表最新的活動
            $productInfo=$promoteGiftRow[sizeof($promoteGiftRow)-1]->name;
            //如果沒有小名的話，用全名
            if(empty($member->nick)) {$nick= $member->name;} else{$nick = $member->nick;}
            //做成最後的型態
            $friendInviteWeb = '嗨！您的好友'.$nick.'邀請您加入CityPass都會通會員，現在註冊立即享有吃喝玩樂優惠！還有好康'.$productInfo.'等你拿！';





            return $this->success([
                    'invitation' => $member->invited_code,
                    'link' => $url.'/invite/'.$member->invited_code,
                    'friendValue' => $friendValue,
                    'friendInviteApp' => $friendInviteApp,
                    'friendInviteWeb' => $friendInviteWeb

                ]);
        }catch (Exception $e){
            return $this->failureCode('E0007');
        }
    }

    public function NoticInfo(Request $request,MemberNoticService $memberNoticService)
    {
        try{
            $params['memberId'] = $request->memberId;
            $params['page'] = empty(!$request->page) ? $request->page : 1;
            $params['limit'] = empty(!$request->limit) ? $request->limit : 20;
            $data = $memberNoticService->memberNoticInfo($params);
            //取得全部訊息數
            $total = $memberNoticService->memberNoticInfoTotal($params);
            $result['total'] = $total;
            $result['limit'] = $params['limit'];
            $result['page'] = (int) $params['page'];
            $result['items'] = (new MemberNoticResult)->list($data);
            return $this->success($result);
        }catch (Exception $e){
            return $this->failureCode('E0007');
        }

    }

    public function readStatusChange(Request $request,MemberNoticService $memberNoticService)
    {
        try{
            $memberId = $request->memberId;
            $notificationId = $request->notificationId;
            //確認是否有此通知
            $isNotic = $memberNoticService->isNotic($memberId, $notificationId);
            if (!$isNotic) return $this->failureCode('E0095');
            $memberNoticService->updateReadStatus($notificationId);
            return $this->success();
        }catch(Exception $e){
            return $this->failureCode('E0007');
        }
    }

    public function memberName(Request $request)
    {
        try{
            $invitation = $request->invitation;
            $member = $this->memberService->findByInvitation($invitation);
            if(empty($member->invited_code))return $this->failureCode('E0092');
            return $this->success([
                    'name' => $member->name
                ]);
        }catch (Exception $e){
            return $this->failureCode('E0061');
        }
    }

}
