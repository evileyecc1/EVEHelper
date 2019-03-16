<?php

namespace App\Console\Commands\ESI\Update;

use App\Jobs\Corporation\GetCorporationDetail;
use Illuminate\Console\Command;

class Corporations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'esi:update:corporations';

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
    public function handle()
    {
        $corporation_ids = \App\Models\Corporations::all(['corporation_id'])->each(function ($value) {
            GetCorporationDetail::dispatch($value->corporation_id);
        });
    }
}
