<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;
use Yadakhov\InsertOnDuplicateKey;

class Corporations extends Model
{
    use Rememberable, InsertOnDuplicateKey;

    protected $primaryKey = 'corporation_id';

    protected $guarded = [];

    public $rememberFor = 3600 * 24;

    public function setDateFoundedAttribute($value)
    {
        $this->attributes['date_founded'] = new Carbon($value);
    }
}
