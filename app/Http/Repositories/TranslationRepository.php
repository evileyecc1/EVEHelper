<?php

namespace App\Http\Repositories;

use App\Models\Translations;

class TranslationRepository
{
    const GROUP = 7;

    const TYPE = 8;

    public function convertTextToID($text_type, $text)
    {
        $translation = Translations::where('tcID', '=', $text_type);

        if (! is_array($text)) {
            return $translation->where('text', $text)->get();
        }

        return $translation->whereIn('text', $text)->get();
    }
}