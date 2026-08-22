<?php
namespace App\Services\Printing;

final class TextEncoder
{
    public function encode(string $text,string $encoding='CP437'): string
    {
        if(preg_match('/[\x{0900}-\x{097F}]/u',$text)) return iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text) ?: '?';
        $out=@iconv('UTF-8',$encoding.'//TRANSLIT//IGNORE',$text);
        return $out===false ? '?' : $out;
    }
    public function hasDevanagari(string $text): bool { return (bool) preg_match('/[\x{0900}-\x{097F}]/u',$text); }
}
