<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Groups extends Model
{
    use Rememberable;

    protected $primaryKey = 'groupID';

    protected $table = 'invGroups';

    public $timestamps = false;

    public $rememberFor = 3600 * 24 * 7;
}
