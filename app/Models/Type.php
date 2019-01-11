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

    public $rememberFor = 10080;

    protected $appends = ['names'];

    public function getNamesAttribute()
    {
        $names = [];
        Translations::where('tcID',8)->where('keyID',$this->attributes['typeID'])->get()->each(function($item,$index) use(&$names){
            $names[$item->languageID] = $item->text;
        });
        return $names;
    }

    public function group()
    {
        return $this->hasOne('App\Models\Groups','groupID','groupID');
    }
}
