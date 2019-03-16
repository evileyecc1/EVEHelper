<?php

namespace App\Console\Commands\ESI\Update;

use App\Jobs\Alliances\GetAllianceDetail;
use ESIHelper\ESIHelper;
use Illuminate\Console\Command;

class Alliances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:alliances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(ESIHelper $ESIHelper)
    {
        $response = $ESIHelper->invoke('get', '/v1/alliances');
        if ($response->status_code == 200 || $response->status_code == 304) {
            $alliances = collect(json_decode($response->response_text, true));
            $this->info('Get total alliance:'.$alliances->count());
            foreach ($alliances as $alliance_id) {
                GetAllianceDetail::dispatch($alliance_id);
            }
        } else {
            $this->error('Response error.Error code:'.$response->status_code.PHP_EOL.'Text:'.$response->response_text);
        }

        return;
    }
}
