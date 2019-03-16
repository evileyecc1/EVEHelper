<?php

namespace App\Jobs\Alliances;

use App\Jobs\Corporation\GetCorporationDetail;
use App\Models\Corporations;
use ESIHelper\ESIHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetAllianceCorporations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alliance_id;

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
    public function handle(ESIHelper $ESIHelper)
    {
        $alliance_id = $this->alliance_id;
        $response = $ESIHelper->invoke('get', '/v1/alliances/{alliance_id}/corporations/', ['alliance_id' => $this->alliance_id]);
        if ($response->status_code == 200 || $response->status_code == 304) {
            $alliance_corporations = json_decode($response->response_text, true);
            $alliance_corps = Corporations::whereAllianceId($alliance_id)->get(['corporation_id']);
            foreach ($alliance_corporations as $corporation) {
                if (! $alliance_corps->has($corporation)) {
                    GetCorporationDetail::dispatch($corporation);
                }
            }
        } else {
            \Log::error('Get Alliance Detail error.Response details:'.json_encode($response));
            self::dispatch($this->alliance_id)->delay(now()->addHour(1));
        }
    }
}
