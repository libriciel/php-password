<?php

declare(strict_types=1);

namespace Libriciel\Password\Service;

/**
 * Cette classe calcule la force et l'entropie d'un mot de passe comme définis
 * sur le site de l'ANSSI.
 *
 * L'entropie (longueur × log2(nombre de symboles)) n'est exacte que pour un mot
 * de passe tiré au hasard. Pour un mot de passe choisi par un utilisateur, c'est
 * un majorant : une condition nécessaire, mais pas suffisante (guide
 * ANSSI-PG-078, §4.1 et recommandation R27).
 *
 * @see {@url https://messervices.cyber.gouv.fr/documents-guides/anssi-guide-authentification_multifacteur_et_mots_de_passe.pdf}
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/} page d'origine des classes de caractères et des seuils, qui
 *      n'est plus en ligne
 */
class PasswordStrengthMeterAnssi implements PasswordStrengthMeterInterface
{
    /**
     * Classes de caractères (expression régulière) et nombre de symboles de
     * chacune, hors binaire et hexadécimal.
     *
     * @var array<string, int>
     */
    private const array PATTERNS = [
        '[0-9]' => 10,
        '[a-z]' => 26,
        '[A-Z]' => 26,
        '[€\\!#\\$\\*%\\? ]' => 8,
        '[&\\[\\|\\]@\\^µ§\\\\:/;\\.,\\<\\>°²³\'"]' => 20,
        '[\\(\\)\\+\\-=_`\\{\\}~]' => 10,
        '[àâäæçèéêëîïôœùûü]' => 16,
        '[ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ]' => 16,
    ];

    /**
     * Retourne le nombre de symboles possibles à tester pour trouver une chaîne
     * de caractères du même type que le mot de passe.
     *
     * Par exemple, "chien" reverra 24, "Chien" renverra 52, "chien6" renverra 36.
     *
     * Les classes de caractères sont les suivantes :
     *  - 0 et 1
     *  - 0 à 9
     *  - 0 à 9, a à f ou A à F
     *  - a à z
     *  - A à Z
     *  - caractères spéciaux
     *      - €!#$*% ?: 8
     *      - &[|]@^µ§\:/;.,<>°²³'": 20
     *      - ()+-=_`{}~: 10
     *      - àâäæçèéêëîïôœùûü: 16
     *      - ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ: 16
     *
     * @param string $string Le mot de passe
     */
    public function symbols(string $string): int
    {
        if (false !== \mb_ereg('^[01]+$', $string)) {
            return 2;
        }

        // Hexadécimal : uniquement des chiffres et des lettres de a à f, dont au moins une lettre
        if (false !== \mb_ereg('^[0-9a-fA-F]*[a-fA-F][0-9a-fA-F]*$', $string)) {
            return false !== \mb_ereg('([A-F].*[a-f]|[a-f].*[A-F])', $string)
                ? 22 : 16;
        }

        $result = 0;
        foreach (self::PATTERNS as $pattern => $symbols) {
            if (false !== \mb_ereg($pattern, $string)) {
                $result += $symbols;
            }
        }

        return $result;
    }

    /**
     * Retourne le nombre de bits d'entropie pour une chaîne de caractères.
     *
     * Renvoie 0 pour une chaîne vide ou sans aucun caractère reconnu par
     * symbols() : le résultat est toujours un nombre fini.
     *
     * @param string $string    La chaîne de caractères
     * @param int    $precision Le nombre de décimales souhaitées
     */
    public function entropy(string $string, int $precision = 0): float
    {
        return \round($this->exactEntropy($string), $precision);
    }

    /**
     * Retourne le nombre de bits d'entropie, sans arrondi.
     *
     * @param string $string La chaîne de caractères
     */
    private function exactEntropy(string $string): float
    {
        $symbols = $this->symbols($string);

        // log(0) vaut -INF, et 0 × -INF vaut NAN pour la chaîne vide
        if (0 === $symbols) {
            return 0.0;
        }

        return \mb_strlen($string) * \log($symbols, 2);
    }

    /**
     * Retourne les valeurs minimales (seuils) d'entropie, en ordre
     * descendant, pour les différentes forces de mots de passe.
     *
     * @return array<int, int>
     */
    public function thresholds(): array
    {
        return [
            128 => static::STRENGTH_VERY_STRONG,
            100 => static::STRENGTH_STRONG,
            80 => static::STRENGTH_MEDIUM,
            64 => static::STRENGTH_WEAK,
            0 => static::STRENGTH_VERY_WEAK,
        ];
    }

    /**
     * Retourne la "force" d'un mot de passe.
     *
     * Il s'agit d'un chiffre entre 1 (très faible) et 5 (très fort) qui
     * correspond aux constantes de classe STRENGTH_VERY_WEAK (1), STRENGTH_WEAK (2),
     * STRENGTH_MEDIUM (3), STRENGTH_STRONG (4) et STRENGTH_VERY_STRONG (5)
     * à partir de l'entropie calculée par la méthode entropy(), sans arrondi :
     * une entropie de 79,9 bits reste sous le seuil de 80 bits.
     *
     * @see PasswordStrengthMeterAnssi::entropy()
     *
     * @param string $string Le mot de passe
     */
    public function strength(string $string): int
    {
        $entropy = $this->exactEntropy($string);

        // Les seuils sont en ordre décroissant : le premier atteint est le bon
        foreach ($this->thresholds() as $threshold => $strength) {
            if ($entropy >= $threshold) {
                return $strength;
            }
        }

        return self::STRENGTH_VERY_WEAK;
    }
}
