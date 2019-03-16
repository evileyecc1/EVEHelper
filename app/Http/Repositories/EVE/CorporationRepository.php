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
        $alliance = new Corporations($corporation_data);

        return $alliance->save();
    }

    public function update($corporation_data)
    {
        $corporation = $this->getOneByID($corporation_data['corporation_id']);
        if ($corporation == null) {
            $this->create($corporation_data);
        } else {
            if (count($this->checkDiff($corporation, $corporation_data)) == 0) {
                return;
            }
            $corporation->update($corporation_data);
        }
    }

    private function checkDiff(Corporations $model, array $data)
    {
        $model_data = $model->getAttributes();
        $model_data = Arr::except($model_data, ['created_at', 'updated_at']);
        $model_data = Arr::where($model_data, function ($value, $key) {
            return $value !== null;
        });
        if (array_key_exists('war_eligible', $data)) {
            $data['war_eligible'] = (int) $data['war_eligible'];
        }
        if (array_key_exists('date_founded', $data)) {
            $data['date_founded'] = (new Carbon($data['date_founded']))->toDateTimeString();
        }

        return array_diff($model_data, $data);
    }
}