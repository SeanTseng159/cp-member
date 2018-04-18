<?php

namespace App\Console\Commands\Carts;

use Illuminate\Console\Command;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Services\CartService;
use App\Console\Commands\Carts\Classes\CleanExpiredCarts\Magento;
use App\Console\Commands\Carts\Classes\CleanExpiredCarts\Citypass;

class CleanExpiredCarts extends Command
{
    public $cartService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:clean_expired_carts {source : ' . ProjectConfig::MAGENTO .' or ' . ProjectConfig::CITY_PASS . '}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove expired carts and notify customers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CartService $cartService)
    {
        parent::__construct();
        $this->cartService = $cartService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source = $this->argument('source');
        switch ($source)
        {
            case ProjectConfig::MAGENTO:
                $expiredDays = 30;
                $cartClassName = 'App\Console\Commands\Carts\Classes\CleanExpiredCarts\Magento';
                break;
            case ProjectConfig::CITY_PASS:
                $expiredDays = 15;
                $cartClassName = 'App\Console\Commands\Carts\Classes\CleanExpiredCarts\Citypass';
                break;
            
            default:
                return false;
        }
        
        $memberIds = $this->cartService->expiredCartMemberIds($source, $expiredDays);
        foreach ($memberIds as $id) {
            $expiredCart = new $cartClassName($id);
            $expiredCart->handle();
        }
    }
    
}
