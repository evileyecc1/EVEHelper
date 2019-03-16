<?php

namespace App\Jobs\Corporation;

use App\Http\Repositories\EVE\CorporationRepository;
use ESIHelper\ESIHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;

class GetCorporationDetail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $corporation_id;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($corporation_id)
    {
        $this->corporation_id = $corporation_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ESIHelper $ESIHelper, CorporationRepository $corporationRepository)
    {
        $response = $ESIHelper->invoke('get', '/v4/corporations/{corporation_id}/', ['corporation_id' => $this->corporation_id]);
        if ($response->status_code == 200 || $response->status_code == 304) {
            $corporation_data = json_decode($response->response_text, true);
            $corporation_data['corporation_id'] = $this->corporation_id;
            $corporation = $corporationRepository->getOneByID($this->corporation_id);
            if ($corporation == null) {
                $corporationRepository->create($corporation_data);
            } else {
                $corporationRepository->update($corporation_data);
            }
        } else {
            \Log::error('Get corporation detail error.Response details:'.json_encode($response));
            self::dispatch($this->corporation_id)->delay(now()->addHour(1));
        }
    }
}
