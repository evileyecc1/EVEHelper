<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Groups extends Model
{
    use Rememberable;

    protected $primaryKey = 'groupID';

    protected $table = 'invgroups';

    public $timestamps = false;

    public $rememberFor = 10080;

    protected $appends = ['names'];

    public function getNamesAttribute()
    {
        $names = [];
        Translations::where('tcID',7)->where('keyID',$this->attributes['groupID'])->get()->each(function($item,$index) use(&$names){
            $names[$item->languageID] = $item->text;
        });
        return $names;
    }
}
