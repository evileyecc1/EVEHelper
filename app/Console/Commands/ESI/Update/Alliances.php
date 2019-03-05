<?php

namespace App\Console\Commands\ESI\Update;

use App\Jobs\Alliances\GetAllianceDetail;
use App\Utils\ESIHelper;
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
        $alliances = $ESIHelper->execute('get','/v1/alliances');
        $alliances = collect(json_decode($alliances,true));
        $this->info('Get total alliance:'.$alliances->count());
        foreach ($alliances as $alliance_id)
        {
            GetAllianceDetail::dispatch($alliance_id);
        }
    }
}
