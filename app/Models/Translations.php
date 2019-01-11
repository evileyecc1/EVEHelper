<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class Translations extends Model
{
    use Rememberable;

    protected $primaryKey = null;

    protected $table = 'trnTranslations';

    public $timestamps = false;

    public $rememberFor = 10080;
}
