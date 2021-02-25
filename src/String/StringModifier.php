<?php

namespace Sprain\SwissQrBill\String;

class StringModifier
{
    public static function replaceLineBreaksWithString($string)
    {
        return str_replace(["\r", "\n"], ' ', $string);
    }

    public static function replaceMultipleSpacesWithOne($string)
    {
        return preg_replace('/ +/', ' ', $string);
    }

    public static function stripWhitespace($string)
    {
        return preg_replace('/\s+/', '', $string);
    }
}
