<?php
namespace Libriciel\Utility\Password;

use Libriciel\Utility\Password\PasswordAbstractMeter;

class PasswordMeterAnssi extends PasswordAbstractMeter
{
    /**
     * {@inheritDoc}
     */
    public static function symbols($string)
    {
        $symbols = 0;
        $alphabetic = false !== mb_eregi('[g-z]', $string);
        $hexadecimal = false !== mb_eregi('[a-f]', $string);
        $result = [
            // 20
            'extra2' => false !== mb_ereg('[&\[\|\]@\^µ§:/;\.,<>°²³\'"]', $string),
            // 8
            'extra1' => false !== mb_ereg('[€!#$*%\? ]', $string),
            // 40 (52 - 12)
            'alphabetic' => $alphabetic,
            // 12 ((16 - 8 - 2) + 6)
            'hexadecimal' => $hexadecimal,
            // 8 (10 - 2)
            'numeric' => false !== mb_ereg('[2-9]', $string),
            // 2
            'binary' => false !== mb_ereg('[0-1]', $string),
            'lower' => $alphabetic || $hexadecimal ? mb_ereg('[a-z]', $string) : null,
            'upper' => $alphabetic || $hexadecimal ? mb_ereg('[A-Z]', $string) : null
        ];

        if (0 < $result['extra2']) {
            $symbols += 20;
        }
        if (0 < $result['extra1']) {
            $symbols += 8;
        }
        if (0 < $result['alphabetic']) {
            $symbols += 26;
            if (0 < $result['lower'] && 0 < $result['upper']) {
                $symbols += 26;
            }
            if (0 < $result['numeric'] || 0 < $result['binary']) {
                $symbols += 10;
            }
        } elseif (0 < $result['hexadecimal']) {
            $symbols += 16;
            if (0 < $result['lower'] && 0 < $result['upper']) {
                $symbols += 6;
            }
        } elseif (0 < $result['numeric']) {
            $symbols += 10;
        } elseif (0 < $result['binary']) {
            $symbols += 2;
        }

        return $symbols;
    }
    

    /**
     * {@inheritDoc}
     */
    public static function thresholds()
    {
        $result = [
            128 => static::STRENGTH_VERY_STRONG,
            100 => static::STRENGTH_STRONG,
            80 => static::STRENGTH_MEDIUM,
            64 => static::STRENGTH_WEAK,
            0 => static::STRENGTH_REALLY_WEAK
        ];
        return $result;
    }
}
