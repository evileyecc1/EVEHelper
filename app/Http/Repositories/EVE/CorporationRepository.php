<?php

namespace App\Http\Repositories\EVE;

use App\Models\Corporations;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class CorporationRepository
{
    public function getOneByID($corporation_id)
    {
        return Corporations::whereCorporationId($corporation_id)->first();
    }

    public function getByID($corporation_ids)
    {
        if (is_iterable($corporation_ids)) {
            return Corporations::whereIn('corporation_id', $corporation_ids)->get();
        }

        return $this->getOneByID($corporation_ids);
    }

    public function create($corporation_data)
    {
        $this->update($corporation_data);
    }

    public function update($corporation_data)
    {
        Corporations::updateOrInsert([
            'corporation_id' => $corporation_data['corporation_id'],
            Arr::except($corporation_data, 'corporation_id'),
        ]);
    }
}