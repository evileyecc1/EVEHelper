<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Type extends Model
{
    use Rememberable;

    protected $primaryKey = 'typeID';

    protected $table = 'invTypes';

    public $timestamps = false;

    public $rememberFor = 3600 * 24 * 7;

    public function group()
    {
        return $this->hasOne('App\Models\Groups', 'groupID', 'groupID');
    }
}
