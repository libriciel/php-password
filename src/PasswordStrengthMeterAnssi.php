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
abstract class PasswordStrengthMeterAnssi implements PasswordStrengthMeterInterface
{

    /**
     * Retourne le nombre de symboles possibles à tester pour trouver une chaîne
     * de caractères du même type que le mot de passe.
     *
     * Par exemple, "chien" reverra 24, "Chien" renverra 52, "chien6" renverra 36.
     *
     * Les classes de caractères sont les suivantes:
     *  - 0 et 1
     *  - 0 à 9
     *  - 0 à 9, a à f ou A à F
     *  - a à z
     *  - A à Z
     *  - caractères spéciaux
     *      - €!#$*% ?: 8
     *      - &[|]@^µ§\:/;.,<>°²³'": 20
     *      - àâäæçèéêëîïôœùûü: 16
     *      - ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ: 16
     *
     * @param string $string Le mot de passe
     * @return int
     */
    public static function symbols($string)
    {
        $hexadecimal = false !== mb_ereg('^[0-9a-fA-F]+$', $string)
            && false !== mb_eregi('([0-9].*[a-f]|[a-f].*[0-9])', $string);

        $result = 0;

        if (false !== mb_ereg('^[01]+$', $string)) {
            $result = 2;
        } elseif (true === $hexadecimal) {
            $result = false !== mb_ereg('([A-F].*[a-f]|[a-f].*[A-F])', $string)
                ? 22 : 16;
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

            foreach ($patterns as $pattern => $symbols) {
                if (false !== mb_ereg($pattern, $string)) {
                    $result += $symbols;
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
        $result = static::STRENGTH_VERY_WEAK;

        foreach ($thresholds as $threshold => $strength) {
            if ($entropy >= $threshold && $strength > $result) {
                $result = $strength;
            }
        }

        return $result;
    }
}
