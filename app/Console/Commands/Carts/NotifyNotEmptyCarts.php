<?php

namespace App\Console\Commands\Carts;

use Illuminate\Console\Command;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Services\CartService;
use Ksd\Mediation\Services\MemberTokenService;
use App\Services\MailService;
use App\Services\MemberService;
use App\Models\Carts;
use Log;

class NotifyNotEmptyCarts extends Command
{

    private $cartService;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:notify_not_empty_carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notify customers who have not empty cart';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
            CartService $cartService,
            MemberTokenService $memberTokenService,
            MailService $mailService,
            MemberService $memberService)
    {
        parent::__construct();
        $this->cartService = $cartService;
        $this->memberTokenService = $memberTokenService;
        $this->mailService = $mailService;
        $this->memberService = $memberService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            Log::info('=== 購物車未結帳提醒 ===');

            // 超過多少天進行提醒
            $notifyIntervalDays = 15;

            // email顯示的商品筆數
            $emailItemLimit = 10;

            // 取得符合寄信提醒條件的購物車
            $needNotifyCarts = Carts::whereRaw('last_notified_at <= DATE_ADD(NOW(), INTERVAL -' . $notifyIntervalDays . ' DAY)')
                    ->get();

            foreach ($needNotifyCarts as $needNotifyCart) {
                Log::debug([$needNotifyCart->member_id, $needNotifyCart->type]);
                switch ($needNotifyCart->type) {
                    case ProjectConfig::MAGENTO:
                        $source = ProjectConfig::MAGENTO;
                        break;

                    default:
                        $source = ProjectConfig::CITY_PASS;
                        break;
                }
                $token = $this->memberTokenService->getUserTokenByMemberId($source, $needNotifyCart->member_id);
                $params = new \stdClass();
                $params->source = $source;
                $member = $this->memberService->find($needNotifyCart->member_id);
                
                if (empty($member)) continue;
                
                $cart = $this->cartService->mine($params, $token);
                $cartItems = array_slice($cart->items, 0, $emailItemLimit);
                $this->mailService->sendNotEmptyCart($member, $cartItems);
                $needNotifyCart->last_notified_at = date('Y-m-d h:i:s');
                $needNotifyCart->save();
            }
            
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
