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
        $alliance = new Alliances($alliance_data);
        return $alliance->save();
    }

    public function update($alliance_data)
    {
        $alliance = $this->getOneByID($alliance_data['alliance_id']);
        if ($alliance == null) {
            $this->create($alliance_data);
        } else {
            if (count($this->checkDiff($alliance, $alliance_data)) == 0) {
                return;
            }
            $alliance->update($alliance_data);
        }
    }

    private function checkDiff(Alliances $model, array $data)
    {
        $model_data = $model->getAttributes();
        $model_data = Arr::except($model_data, ['created_at', 'updated_at']);
        $model_data = Arr::where($model_data, function ($value, $key) {
            return $value !== null;
        });
        if (array_key_exists('date_founded', $data)) {
            $data['date_founded'] = (new Carbon($data['date_founded']))->toDateTimeString();
        }

        return array_diff($model_data, $data);
    }
}