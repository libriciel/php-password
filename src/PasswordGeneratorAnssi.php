<?php

namespace Libriciel\Utility\Password;

abstract class PasswordGeneratorAnssi
{
    protected static $classes = [
        'binary'  => '01',
        'numbers'  => '0123456789',
        'lowercase_hexadecimal' => '0123456789abcdef',
        'uppercase_hexadecimal' => '0123456789ABCDEF',
        'lowercase_letters' => 'abcdefghijklmnopqrstuvwxyz',
        'uppercase_letters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'symbols_1' => '€!#$*% ?',
        'symbols_2' => '&[|]@^µ§\:/;.,<>°²³\'"',
        'lowercase_accents_fr' => 'àâäæçèéêëîïôœùûü',
        'uppercase_accents_fr' => 'ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ'
    ];
    
    public static function classes(array $params = [])
    {
        $params += array_combine(
            array_keys(static::$classes),
            array_fill(0, count(static::$classes), true)
        );

        $result = [];
        foreach(array_keys(static::$classes) as $key) {
            if(true === isset($params[$key]) && true === $params[$key]) {
                $result[] = $key;
            }
        }
        return $result;
    }

    public static function characters(array $params = [], $encoding = null)
    {
        $classes = static::classes($params);
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;        
        $accumulator = [];

        foreach($classes as $key) {
            $accumulator = array_merge(
                $accumulator,
                mb_str_split(static::$classes[$key], 1, $encoding)
            );
        }

        return array_values(array_unique($accumulator));
    }

    public static function generate($length, array $params = [], $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $characters = static::characters($params, $encoding);
        $count = count($characters);
        $result = false;

        if($count > 0) {
            $result = '';
            $max = 0;
            while($max<$length) {
                $random = random_int(0, $count-1);
                $result .= $characters[$random];
                $max++;
            }
        }

        return $result;
    }
}

