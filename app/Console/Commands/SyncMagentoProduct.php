<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MagentoProductService;
use Log;

class SyncMagentoProduct extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:sync_magento_product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize magento products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MagentoProductService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("=============== Synchronize magento products start ===============");
        $this->service->syncAll();
        Log::info("=============== Synchronize magento products end ===============");
    }
}
