<?php

/**
 * Code source de la classe Libriciel\Utility\Password\PasswordStrengthMeterAnssi
 *
 * @license CeCiLL V2
 */

namespace Libriciel\Utility\Password;

use Libriciel\Utility\Password\PasswordStrengthMeterInterface;

/**
 * Cette classe calcule la force et l'entropie d'un mot de passe comme définis
 * sur le site de l'ANSSI.
 *
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/}
 */
class PasswordStrengthMeterAnssi implements PasswordStrengthMeterInterface
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
     * Retourne le nombre de bits d'entropie pour une chaîne de caractères.
     *
     * @param string $string La chaîne de caractères
     * @return integer
     */
    public static function entropy($string)
    {
        $length = mb_strlen($string);
        $symbols = static::symbols($string);
        return (int)round($length*log($symbols, 2));
    }

    /**
     * Retourne les valeurs minimales (seuils) d'entropie, en ordre
     * descendant, pour les différentes forces de mots de passe.
     *
     * @return array
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
    
    /**
     * Retourne la "force" d'un mot de passe.
     *
     * Il s'agit d'un chiffre entre 1 (très faible) et 5 (très fort) qui
     * correspond au constantes de classe STRENGTH_VERY_WEAK (1), STRENGTH_WEAK (2),
     * STRENGTH_MEDIUM (3), STRENGTH_STRONG (4) et STRENGTH_VERY_STRONG (5)
     * à partir de l'entropie calculée par la méthode entropy().
     *
     * @see PasswordAnssi::entropy()
     *
     * @param string $string Le mot de passe
     * @return integer
     */
    public static function strength($string)
    {
        $entropy = static::entropy($string);
        $thresholds = static::thresholds();

        foreach ($thresholds as $threshold => $strength) {
            if ($entropy >= $threshold) {
                return $strength;
            }
        }

        return static::STRENGTH_VERY_WEAK;
    }
}
