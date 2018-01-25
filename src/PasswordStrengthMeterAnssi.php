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

        $hexadecimal = false !== mb_ereg('^[0-9a-fA-F]+$', $string)
            && false !== mb_ereg('[0-9]', $string)
            && (
                true === $lower
                || true === $upper
            );

        $result = 0;

        if (false !== mb_ereg('^[01]+$', $string)) {
            $result = 2;
        } elseif (true === $hexadecimal) {
            $result = (true === $lower && true === $upper) ? 22 : 16;
        } else {
            $patterns = [
                '[0-9]' => 10,
                '[a-z]' => 26,
                '[A-Z]' => 26,
                '[€\\!#\\$\\*%\\? ]' => 8,
                '[&\\[\\|\\]@\\^µ§\\:/;\\.,\\<\\>°²³\'"]' => 20,
                '[àâäæçèéêëîïôœùûü]' => 16,
                '[ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ]' => 16
            ];

            foreach ($patterns as $pattern => $entropy) {
                if (false !== mb_ereg($pattern, $string)) {
                    $result += $entropy;
                }
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
