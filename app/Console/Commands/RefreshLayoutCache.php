<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

use App\Jobs\Cache\RefreshLayoutAllCache as AllCacheJob;

class RefreshLayoutCache extends Command
{
    private $magentoOrder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:refresh_layout_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refresh layout all cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('====== Refresh Layout Cache Start ======');

        dispatch(new AllCacheJob);

        Log::info('====== Refresh Layout Cache End ======');
    }
}
