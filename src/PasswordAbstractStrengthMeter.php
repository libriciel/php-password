<?php
namespace Libriciel\Utility\Password;

use Libriciel\Utility\Password\PasswordStrengthMeterInterface;

abstract class PasswordAbstractStrengthMeter implements PasswordStrengthMeterInterface
{
    /**
     * Retourne le nombre de bits d'entropie pour une chaîne de caractères.
     *
     * @param string $string La chaîne de caractères
     * @return integer
     */
    public static function entropyBits($string)
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
    abstract public static function thresholds();

    /**
     * Retourne la "force" d'un mot de passe.
     *
     * Il s'agit d'un chiffre entre 1 (très faible) et 5 (très fort) qui
     * correspond au constantes de classe STRENGTH_VERY_WEAK (1), STRENGTH_WEAK (2),
     * STRENGTH_MEDIUM (3), STRENGTH_STRONG (4) et STRENGTH_VERY_STRONG (5)
     * à partir de l'entropie calculée par la méthode entropyBits().
     *
     * @see PasswordAnssi::entropyBits()
     *
     * @param string $string Le mot de passe
     * @return integer
     */
    public static function strength($string)
    {
        $entropyBits = static::entropyBits($string);
        $thresholds = static::thresholds();

        foreach ($thresholds as $threshold => $strength) {
            if ($entropyBits >= $threshold) {
                return $strength;
            }
        }

        return static::STRENGTH_VERY_WEAK;
    }
}
