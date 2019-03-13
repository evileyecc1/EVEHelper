<?php

namespace App\Jobs\Alliances;

use App\Http\Repositories\EVE\AllianceRepository;
use App\Models\Alliances;
use Illuminate\Support\Arr;
use ESIHelper\ESIHelper;
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
        $alliance_info = $ESIHelper->invoke('get', '/v3/alliances/{alliance_id}/', ['alliance_id' => $this->alliance_id]);
        if($alliance_info->status_code == 200 || $alliance_info->status_code == 304){
            $alliance_data = json_decode($alliance_info->response_text, true);
            $alliance = $allianceRepository->getOneByID($this->alliance_id);
            if ($alliance == null) {
                $allianceRepository->create($this->alliance_id, $alliance_data);
            } else {
                if(! $alliance_info->is_cached)
                $alliance->update(Arr::except($alliance_data,'alliance_id'));
            }
        }
        else{
            \Log::error('Get Alliance Detail error.Response details:'.json_encode($alliance_info));
            GetAllianceDetail::dispatch($this->alliance_id)->delay(now()->addHour(1));
        }
    }
}
