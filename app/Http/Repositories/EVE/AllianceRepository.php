<?php

namespace App\Http\Repositories\EVE;

use App\Models\Alliances;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AllianceRepository
{
    public function getOneByID($alliance_id)
    {
        return Alliances::find($alliance_id);
    }

    public function getByID($alliance_ids)
    {
        if(is_iterable($alliance_ids))
            return Alliances::whereIn('alliance_id',$alliance_ids)->get();
        return $this->getOneByID($alliance_ids);
    }

    public function create($alliance_data)
    {
        $this->update($alliance_data);
    }

    public function update($alliance_data)
    {
        Alliances::updateOrInsert(['alliance_id' => $alliance_data['alliance_id']], Arr::except($alliance_data, 'alliance_id'));
    }
}