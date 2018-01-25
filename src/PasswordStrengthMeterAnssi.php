<?php

/**
 * Code source de la classe Libriciel\Utility\Password\PasswordStrengthMeterAnssi
 *
 * @license CeCiLL V2
 */

namespace Libriciel\Utility\Password;

use Libriciel\Utility\Password\PasswordAbstractStrengthMeter;

/**
 * Cette classe calcule la force et l'entropie d'un mot de passe comme définis
 * sur le site de l'ANSSI.
 *
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/}
 */
class PasswordStrengthMeterAnssi extends PasswordAbstractStrengthMeter
{

    /**
     * {@inheritDoc}
     * @param string $string La chaîne de caractères
     * @return int
     */
    public static function symbols($string)
    {
        $lower = false !== mb_ereg('[a-z]', $string);
        $upper = false !== mb_ereg('[A-Z]', $string);

        $letters = true === $lower || true === $upper;
        $mixed = true === $lower && true === $upper;
        $numbers = false !== mb_ereg('[0-9]', $string);

        $onlyHexadecimal = false !== mb_ereg('^[0-9a-fA-F]+$', $string);

        if (false !== mb_ereg('^[01]+$', $string)) {
            $result = 2;
        } elseif (true === $onlyHexadecimal && true === $numbers && true === $mixed) {
            $result = 22;
        } elseif (true === $onlyHexadecimal && true === $numbers && true === $letters) {
            $result = 16;
        } else {
            $result = 0;

            if (true === $numbers) {
                $result += 10;
            }

            $result += ((true === $lower ? 26 : 0) + (true === $upper ? 26 : 0));

            if (false !== mb_ereg('[€\\!#\\$\\*%\\? ]', $string)) {
                $result += 8;
            }

            if (false !== mb_ereg('[&\\[\\|\\]@\\^µ§\\:/;\\.,\\<\\>°²³\'"]', $string)) {
                $result += 20;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string $string Le mot de passe
     * @return integer
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
