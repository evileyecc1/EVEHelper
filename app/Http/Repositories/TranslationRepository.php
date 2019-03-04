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

    public function getTranslation($text_type,$id,$language = ['en','zh']){
        if(!is_iterable($id))
            $id = [$id];

        $translation = Translations::where('tcID','=',$text_type)->whereIn('languageID',$language);

        $result = collect();

        $translations =  $translation->whereIn('keyID',$id)->get();

        foreach ($translations as $item){
            $temp = $result->get($item->keyID);
            $temp[$item->languageID] = $item->text;
            $result->put($item->keyID,$temp);
        }

        return $result;
    }
}