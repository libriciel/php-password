<?php

namespace Libriciel\Utility\Password;

/**
 * Une interface pour les classes qui retournent la force d'un mot de passe.
 */
interface PasswordStrengthMeterInterface
{
    /**
     * La constante utilisée pour une force de mot de passe "très forte".
     */
    public const STRENGTH_VERY_STRONG = 5;

    /**
     * La constante utilisée pour une force de mot de passe "forte".
     */
    public const STRENGTH_STRONG = 4;

    /**
     * La constante utilisée pour une force de mot de passe "moyenne".
     */
    public const STRENGTH_MEDIUM = 3;

    /**
     * La constante utilisée pour une force de mot de passe "faible".
     */
    public const STRENGTH_WEAK = 2;

    /**
     * La constante utilisée pour une force de mot de passe "très faible".
     */
    public const STRENGTH_VERY_WEAK = 1;

    /**
     * Retourne le nombre estimé de symboles à tester pour la chaîne de
     * caractères.
     *
     * @param string $string La chaîne de caractères
     * @return int
     */
    public function symbols(string $string): int;

    /**
     * Retourne la force d'un mot de passe.
     *
     * Il s'agit d'un chiffre entre 1 (très faible) et 5 (très fort) qui
     * correspond aux constantes de classe STRENGTH_VERY_WEAK (1), STRENGTH_WEAK (2),
     * STRENGTH_MEDIUM (3), STRENGTH_STRONG (4) et STRENGTH_VERY_STRONG (5).
     *
     * @param string $string Le mot de passe
     * @return integer
     */
    public function strength(string $string): int;
}
