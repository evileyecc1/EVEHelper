<?php

namespace App\Jobs\Alliances;

use App\Http\Repositories\EVE\AllianceRepository;
use App\Models\Alliances;
use App\Utils\ESIHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetAllianceDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alliance_id;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($alliance_id)
    {
        $this->alliance_id = $alliance_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ESIHelper $ESIHelper, AllianceRepository $allianceRepository)
    {
        $alliance_info = $ESIHelper->execute('get', '/v3/alliances/{alliance_id}/', ['alliance_id' => $this->alliance_id]);
        $alliance_info = json_decode($alliance_info, true);
        $alliance = $allianceRepository->getOneByID($this->alliance_id);
        if ($alliance == null) {
            $allianceRepository->create($this->alliance_id, $alliance_info);
        } else {
            $alliance->update(array_except($alliance_info,'alliance_id'));
        }
    }
}
