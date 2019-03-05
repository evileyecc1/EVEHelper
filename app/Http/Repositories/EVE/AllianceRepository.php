<?php

namespace App\Http\Repositories\EVE;

use App\Models\Alliances;
use Carbon\Carbon;

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

    public function create($alliance_id,$data)
    {
        $data['alliance_id'] = $alliance_id;
        $alliance = new Alliances($data);
        return $alliance->save();
    }
}