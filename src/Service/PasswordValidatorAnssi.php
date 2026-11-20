<?php

declare(strict_types=1);

namespace Libriciel\Password\Service;

use Libriciel\Password\Dictionary\FileDictionary;
use Libriciel\Password\Dictionary\PasswordDictionaryInterface;
use Libriciel\Password\PasswordException;

/**
 * Vérifie un mot de passe choisi par un utilisateur, au moment de sa création
 * ou de son renouvellement, selon le guide ANSSI-PG-078 v2.0 :
 *  - longueur maximale, contre le déni de service (R22) ;
 *  - entropie minimale (R21, voir PasswordStrengthMeterAnssi) ;
 *  - règles de complexité (R23), assouplies pour les phrases de passe ;
 *  - contrôle de robustesse (R27) : motifs, dictionnaire de mots de passe
 *    courants, informations personnelles.
 *
 * La réutilisation des anciens mots de passe (R27) reste du ressort de
 * l'application, qui seule détient l'historique des empreintes.
 *
 * @see {@url https://messervices.cyber.gouv.fr/documents-guides/anssi-guide-authentification_multifacteur_et_mots_de_passe.pdf}
 */
final readonly class PasswordValidatorAnssi
{
    /**
     * Le mot de passe dépasse la longueur maximale (R22) : les autres
     * contrôles ne sont pas effectués.
     */
    public const string VIOLATION_LENGTH = 'length';

    /**
     * L'entropie est inférieure à l'entropie demandée (R21).
     */
    public const string VIOLATION_ENTROPY = 'entropy';

    /**
     * Le mot de passe utilise trop peu de classes de caractères (R23).
     */
    public const string VIOLATION_COMPLEXITY = 'complexity';

    /**
     * Le mot de passe contient une suite, une ligne de clavier ou une répétition (R27).
     */
    public const string VIOLATION_PATTERN = 'pattern';

    /**
     * Le mot de passe figure dans le dictionnaire, éventuellement déguisé (R27).
     */
    public const string VIOLATION_DICTIONARY = 'dictionary';

    /**
     * Le mot de passe contient une information personnelle (R27).
     */
    public const string VIOLATION_PERSONAL_DATA = 'personal_data';

    /**
     * Suites et lignes de clavier (AZERTY et QWERTY), recherchées dans les
     * deux sens.
     */
    private const array SEQUENCES = [
        'abcdefghijklmnopqrstuvwxyz',
        '01234567890',
        'azertyuiop',
        'qsdfghjklm',
        'wxcvbn',
        'qwertyuiop',
        'asdfghjkl',
        'zxcvbnm',
    ];

    /**
     * Substitutions courantes (leet speak), hors « 1 » qui peut valoir « i » ou « l ».
     */
    private const array LEET = [
        '@' => 'a',
        '4' => 'a',
        '3' => 'e',
        '€' => 'e',
        '0' => 'o',
        '$' => 's',
        '5' => 's',
        '7' => 't',
        '!' => 'i',
        '|' => 'i',
    ];

    private const array ACCENTS = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'æ' => 'ae', 'œ' => 'oe',
    ];

    /**
     * Longueur minimale d'une partie du mot de passe comparée au dictionnaire,
     * une fois retirés les chiffres et symboles en début et en fin.
     */
    private const int MIN_CORE_LENGTH = 4;

    /**
     * Longueur minimale d'une information personnelle recherchée.
     */
    private const int MIN_PERSONAL_DATA_LENGTH = 3;

    private PasswordStrengthMeterAnssi $meter;

    private PasswordDictionaryInterface $dictionary;

    /**
     * @param PasswordDictionaryInterface|null $dictionary       Le dictionnaire (par défaut, les listes livrées avec la bibliothèque)
     * @param int                              $minClasses       Nombre de classes de caractères exigées parmi minuscules, majuscules, chiffres et autres caractères (R23)
     * @param int                              $passphraseLength Longueur à partir de laquelle les classes ne sont plus exigées (phrase de passe, R23)
     * @param int                              $patternLength    Longueur minimale d'une suite, d'une ligne de clavier ou d'une répétition d'un même caractère (R27)
     * @param int                              $maxLength        Longueur maximale, contre le déni de service (R22 : plusieurs centaines de caractères)
     * @param PasswordStrengthMeterAnssi|null  $meter            Le calcul d'entropie (par défaut, PasswordStrengthMeterAnssi)
     */
    public function __construct(
        ?PasswordDictionaryInterface $dictionary = null,
        private int $minClasses = 3,
        private int $passphraseLength = 20,
        private int $patternLength = 4,
        private int $maxLength = 1024,
        ?PasswordStrengthMeterAnssi $meter = null,
    ) {
        if ($minClasses < 0 || $minClasses > 4) {
            throw new PasswordException(sprintf('Le nombre de classes exigées doit être compris entre 0 et 4 (%d)', $minClasses));
        }
        if ($passphraseLength < 1 || $patternLength < 2) {
            throw new PasswordException(sprintf('Longueurs invalides : phrase de passe %d (minimum 1), motif %d (minimum 2)', $passphraseLength, $patternLength));
        }
        if ($maxLength < 1) {
            throw new PasswordException(sprintf('La longueur maximale doit être au moins de 1 (%d)', $maxLength));
        }

        $this->dictionary = $dictionary ?? new FileDictionary();
        $this->meter = $meter ?? new PasswordStrengthMeterAnssi();
    }

    /**
     * Indique si le mot de passe respecte l'entropie demandée et les
     * recommandations R22, R23 et R27 : regroupe validLength(),
     * validComplexity(), validPattern(), validDictionary() et les contrôles
     * d'entropie et d'informations personnelles.
     *
     * @param string       $password     Le mot de passe
     * @param float        $entropy      L'entropie minimale, en bits (par exemple 80)
     * @param list<string> $personalData Les informations personnelles de l'utilisateur (nom, prénom, identifiant, e-mail, date de naissance…)
     */
    public function validPassword(string $password, float $entropy, array $personalData = []): bool
    {
        return [] === $this->violations($password, $entropy, $personalData);
    }

    /**
     * Retourne la liste des règles non respectées (constantes VIOLATION_*),
     * vide si le mot de passe est valide, pour que l'application affiche un
     * message adapté.
     *
     * Un mot de passe trop long renvoie seulement VIOLATION_LENGTH : les autres
     * contrôles, plus coûteux, ne sont pas effectués.
     *
     * @param string       $password     Le mot de passe
     * @param float        $entropy      L'entropie minimale, en bits (par exemple 80)
     * @param list<string> $personalData Les informations personnelles de l'utilisateur (nom, prénom, identifiant, e-mail, date de naissance…)
     *
     * @return list<string>
     */
    public function violations(string $password, float $entropy, array $personalData = []): array
    {
        if (!$this->validLength($password)) {
            return [self::VIOLATION_LENGTH];
        }

        $violations = [];
        $lower = mb_strtolower($password);

        if ($this->meter->entropy($password, 10) < $entropy) {
            $violations[] = self::VIOLATION_ENTROPY;
        }
        if (!$this->validComplexity($password)) {
            $violations[] = self::VIOLATION_COMPLEXITY;
        }
        if (!$this->validPattern($password)) {
            $violations[] = self::VIOLATION_PATTERN;
        }
        if (!$this->validDictionary($password)) {
            $violations[] = self::VIOLATION_DICTIONARY;
        }
        if ($this->containsPersonalData($lower, $personalData)) {
            $violations[] = self::VIOLATION_PERSONAL_DATA;
        }

        return $violations;
    }

    /**
     * Indique si le mot de passe ne dépasse pas $maxLength caractères (R22).
     *
     * validComplexity(), validPattern() et validDictionary() n'appliquent pas
     * cette limite : appeler validLength() avant elles pour une saisie
     * utilisateur.
     *
     * @param string $password Le mot de passe
     */
    public function validLength(string $password): bool
    {
        return mb_strlen($password) <= $this->maxLength;
    }

    /**
     * Indique si le mot de passe respecte les règles de complexité (R23) : au
     * moins $minClasses classes parmi minuscules, majuscules, chiffres et
     * autres caractères, sauf pour une phrase de passe d'au moins
     * $passphraseLength caractères.
     *
     * Échec signalé par violations() avec le code VIOLATION_COMPLEXITY.
     *
     * @param string $password Le mot de passe
     */
    public function validComplexity(string $password): bool
    {
        if (mb_strlen($password) >= $this->passphraseLength) {
            return true;
        }

        $classes = 0;
        foreach (['/\p{Ll}/u', '/\p{Lu}/u', '/\p{Nd}/u', '/[^\p{Ll}\p{Lu}\p{Nd}]/u'] as $pattern) {
            $classes += preg_match($pattern, $password);
        }

        return $classes >= $this->minClasses;
    }

    /**
     * Indique si le mot de passe ne contient aucun motif (R27) : suite (abcd,
     * 1234), ligne de clavier (azer, qwer) d'au moins $patternLength
     * caractères, répétition d'un même caractère (aaaa), ou mot de passe formé
     * d'un bloc répété (abcabc).
     *
     * Échec signalé par violations() avec le code VIOLATION_PATTERN.
     *
     * @param string $password Le mot de passe
     */
    public function validPattern(string $password): bool
    {
        return !$this->hasPattern(mb_strtolower($password));
    }

    private function hasPattern(string $lower): bool
    {
        $length = $this->patternLength;

        if (1 === preg_match('/(.)\1{'.($length - 1).',}/us', $lower)
            || 1 === preg_match('/^(.+)\1+$/us', $lower)
        ) {
            return true;
        }

        $chars = mb_str_split($lower);
        for ($i = 0, $max = count($chars) - $length; $i <= $max; ++$i) {
            $chunk = implode('', array_slice($chars, $i, $length));
            foreach (self::SEQUENCES as $sequence) {
                if (str_contains($sequence, $chunk) || str_contains(strrev($sequence), $chunk)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Indique si le mot de passe est absent du dictionnaire (R27), y compris
     * ses variantes sans leet speak (P@ssw0rd) et sa partie centrale sans les
     * chiffres et symboles ajoutés au début ou à la fin (Marseille2024!), sans
     * tenir compte de la casse.
     *
     * Échec signalé par violations() avec le code VIOLATION_DICTIONARY.
     *
     * @param string $password Le mot de passe
     */
    public function validDictionary(string $password): bool
    {
        return !$this->isInDictionary(mb_strtolower($password));
    }

    private function isInDictionary(string $lower): bool
    {
        $candidates = $this->withLeetVariants($lower);

        $core = (string) preg_replace('/^[^\p{L}]+|[^\p{L}]+$/u', '', $lower);
        if (mb_strlen($core) >= self::MIN_CORE_LENGTH) {
            $candidates = [...$candidates, ...$this->withLeetVariants($core)];
        }

        foreach (array_unique($candidates) as $candidate) {
            if ($this->dictionary->contains($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * R27 : nom, prénom, identifiant, parties d'une adresse e-mail ou d'une
     * date, sans tenir compte de la casse, des accents ni du leet speak.
     *
     * @param list<string> $personalData
     */
    private function containsPersonalData(string $lower, array $personalData): bool
    {
        $haystacks = $this->withLeetVariants($this->removeAccents($lower));

        foreach ($personalData as $data) {
            foreach ($this->personalTokens($data) as $token) {
                foreach ($haystacks as $haystack) {
                    if (str_contains($haystack, $token)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Découpe une information personnelle en mots (jean-pierre.dupont@exemple.fr
     * donne jean, pierre, dupont, exemple) et ajoute sa version sans séparateurs
     * (12/03/1985 donne 12031985).
     *
     * @return list<string>
     */
    private function personalTokens(string $data): array
    {
        $data = $this->removeAccents(mb_strtolower($data));
        $parts = preg_split('/[^\p{L}\p{Nd}]+/u', $data) ?: [];
        $parts[] = implode('', $parts);

        return array_values(array_filter(
            array_unique($parts),
            static fn (string $part): bool => mb_strlen($part) >= self::MIN_PERSONAL_DATA_LENGTH
        ));
    }

    /**
     * @return list<string>
     */
    private function withLeetVariants(string $string): array
    {
        $leet = strtr($string, self::LEET);

        return [$string, strtr($leet, ['1' => 'i']), strtr($leet, ['1' => 'l'])];
    }

    private function removeAccents(string $string): string
    {
        return strtr($string, self::ACCENTS);
    }
}
