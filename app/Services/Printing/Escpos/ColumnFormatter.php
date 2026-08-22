<?php
namespace App\Services\Printing\Escpos;

final class ColumnFormatter
{
    public function __construct(private int $width) {}
    public function wrap(string $text): array { $text=trim(preg_replace('/\s+/u',' ',$text)??''); if($text==='') return ['']; $out=[]; while(mb_strlen($text)>$this->width){ $cut=$this->width; $space=mb_strrpos(mb_substr($text,0,$this->width+1),' '); if($space!==false && $space>0) $cut=$space; $out[]=rtrim(mb_substr($text,0,$cut)); $text=ltrim(mb_substr($text,$cut)); } $out[]=$text; return $out; }
    public function left(string $s): string { return str_pad(mb_substr($s,0,$this->width),$this->width); }
    public function right(string $s): string { return str_pad(mb_substr($s,0,$this->width),$this->width,' ',STR_PAD_LEFT); }
    public function center(string $s): string { $s=mb_substr($s,0,$this->width); $left=max(0,intdiv($this->width-mb_strlen($s),2)); return str_repeat(' ',$left).$s.str_repeat(' ',$this->width-$left-mb_strlen($s)); }
    public function separator(string $char='-'): string { return str_repeat($char,$this->width); }
    public function twoColumn(string $label,string $value): string { $value=mb_substr($value,0,$this->width); $spaces=max(1,$this->width-mb_strlen($label)-mb_strlen($value)); return mb_substr($label,0,$this->width-$spaces-mb_strlen($value)).str_repeat(' ',$spaces).$value; }
    public function itemRow(string $name,string $qty,string $price): array { $q=max(1,mb_strlen($qty)); $p=max(1,mb_strlen($price)); $gap=2; $nameWidth=$this->width-$q-$p-$gap*2; $lines=$this->wrapTo($name,$nameWidth); $out=[]; foreach($lines as $i=>$line){ $out[]=str_pad(mb_substr($line,0,$nameWidth),$nameWidth).'  '.($i===0?str_pad($qty,$q,' ',STR_PAD_LEFT):str_repeat(' ',$q)).'  '.($i===count($lines)-1?str_pad($price,$p,' ',STR_PAD_LEFT):str_repeat(' ',$p)); } return $out; }
    private function wrapTo(string $text,int $width): array { $old=$this->width; $this->width=$width; $r=$this->wrap($text); $this->width=$old; return $r; }
}
