<?php

namespace App\Jobs;

use GuzzleHttp\Client as CollectionClient;
use GuzzleHttp\RequestOptions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CollectAiData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    
    /**
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new CollectionClient)->request('POST', 'http://galera.touchcity.tw/api/v1/data/collect', [RequestOptions::JSON => $this->data]);
    }
}
