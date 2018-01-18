<?php
namespace Libriciel\Utility\Password;

use Libriciel\Utility\Password\PasswordAbstractStrengthMeter;

class PasswordStrengthMeterAnssi extends PasswordAbstractStrengthMeter
{
    /**
     * Retourne un array contenant un indicateur de présence des classes de
     * caractères présentes dans la chaîne.
     *
     * Les noms des classes sont: binary, numeric, lower, upper, mixed_cases,
     * hexadecimal, alphabetic, extra1 et extra2.
     *
     * @param string $string
     * @return array
     */
    protected static function classes($string)
    {
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
            'lower' => $alphabetic || $hexadecimal
                ? false !== mb_ereg('[a-z]', $string)
                : false,
            'upper' => $alphabetic || $hexadecimal
                ? false !== mb_ereg('[A-Z]', $string)
                : false
        ];
        
        $result['mixed_cases'] = true === $result['lower']
            && true === $result['upper'];

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public static function symbols($string)
    {
        $classes = static::classes($string);
        $result = 0;

        if (true === $classes['extra2']) {
            $result += 20;
        }

        if (true === $classes['extra1']) {
            $result += 8;
        }
        
        if (true === $classes['alphabetic'] || true === $classes['hexadecimal']) {
            $result += (
                (true === $classes['alphabetic'] ? 26 : 6)
                * (true === $classes['mixed_cases'] ? 2 : 1)
            );
            if (true === $classes['numeric'] || true === $classes['binary']) {
                $result += 10;
            }
        } elseif (true === $classes['numeric']) {
            $result += 10;
        } elseif (true === $classes['binary']) {
            $result += 2;
        }

        return $result;
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
            0 => static::STRENGTH_VERY_WEAK
        ];
        return $result;
    }
}
