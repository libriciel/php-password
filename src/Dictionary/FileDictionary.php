<?php

declare(strict_types=1);

namespace Libriciel\Password\Dictionary;

use Libriciel\Password\PasswordException;

/**
 * Dictionnaire lu depuis des fichiers texte, un mot de passe par ligne.
 *
 * Sans argument, il utilise les listes livrées avec la bibliothèque (voir
 * resources/dictionaries/NOTICE.md) :
 *  - les mots de passe les plus courants en France (projet richelieu, CC BY 4.0) ;
 *  - les 10 000 mots de passe les plus courants de zxcvbn (Dropbox, MIT).
 *
 * Les fichiers ne sont lus qu'à la première recherche, puis gardés en mémoire.
 */
final class FileDictionary implements PasswordDictionaryInterface
{
    /**
     * @var list<string>
     */
    public const array DEFAULT_FILES = [
        __DIR__.'/../../resources/dictionaries/richelieu-fr.txt',
        __DIR__.'/../../resources/dictionaries/zxcvbn-en.txt',
    ];

    /**
     * @var array<array-key, true>|null
     */
    private ?array $entries = null;

    /**
     * @param list<string> $files Les chemins des fichiers, encodés en UTF-8
     */
    public function __construct(private readonly array $files = self::DEFAULT_FILES)
    {
    }

    public function contains(string $password): bool
    {
        return isset($this->entries()[$password]);
    }

    /**
     * @return array<array-key, true>
     */
    private function entries(): array
    {
        if (null === $this->entries) {
            $entries = [];
            foreach ($this->files as $file) {
                $lines = is_readable($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : false;
                if (false === $lines) {
                    throw new PasswordException(sprintf('Impossible de lire le dictionnaire %s', $file));
                }
                foreach ($lines as $line) {
                    $entries[mb_strtolower(trim($line))] = true;
                }
            }
            $this->entries = $entries;
        }

        return $this->entries;
    }
}
