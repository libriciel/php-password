<?php

declare(strict_types=1);

namespace Libriciel\Password\Dictionary;

/**
 * Dictionnaire de mots de passe courants ou compromis (guide ANSSI-PG-078,
 * recommandation R27).
 *
 * L'application peut fournir sa propre implémentation : liste interne,
 * Have I Been Pwned (k-anonymat), etc.
 */
interface PasswordDictionaryInterface
{
    /**
     * Indique si le dictionnaire contient le mot de passe.
     *
     * @param string $password Le mot de passe, déjà passé en minuscules
     */
    public function contains(string $password): bool;
}
