<?php
namespace Libriciel\Utility\Password;

interface PasswordMeterInterface
{
    /**
     * La constante utilisée pour une force de mot de passe "très forte".
     */
    const STRENGTH_VERY_STRONG = 5;

    /**
     * La constante utilisée pour une force de mot de passe "forte".
     */
    const STRENGTH_STRONG = 4;

    /**
     * La constante utilisée pour une force de mot de passe "moyenne".
     */
    const STRENGTH_MEDIUM = 3;

    /**
     * La constante utilisée pour une force de mot de passe "faible".
     */
    const STRENGTH_WEAK = 2;

    /**
     * La constante utilisée pour une force de mot de passe "très faible".
     */
    const STRENGTH_REALLY_WEAK = 1;
    
    /**
     * Retourne le nombre estimé de symboles à tester pour la chaîne de
     * caractères.
     *
     * @param string $string La chaîne de caractères
     * @return int
     */
    public static function symbols($string);
    
    /**
     * Retourne la force d'un mot de passe.
     *
     * Il s'agit d'un chiffre entre 1 (très faible) et 5 (très fort) qui
     * correspond au constantes de classe STRENGTH_REALLY_WEAK (1), STRENGTH_WEAK (2),
     * STRENGTH_MEDIUM (3), STRENGTH_STRONG (4) et STRENGTH_VERY_STRONG (5).
     *
     * @param string $string Le mot de passe
     * @return integer
     */
    public static function strength($string);
}
