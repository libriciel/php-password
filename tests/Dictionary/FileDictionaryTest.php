<?php

declare(strict_types=1);

namespace LibricielTest\Password\Dictionary;

use Libriciel\Password\Dictionary\FileDictionary;
use Libriciel\Password\PasswordException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\Dictionary\FileDictionary.
 */
class FileDictionaryTest extends TestCase
{
    /**
     * @return array<string, array{bool, string}>
     */
    public static function defaultDictionaryProvider(): array
    {
        return [
            'français 1' => [true, 'azerty'],
            'français 2' => [true, 'motdepasse'],
            'français 3' => [true, 'marseille'],
            'français avec accent' => [true, 'liberté'],
            'international 1' => [true, 'password'],
            'international 2' => [true, 'qwerty'],
            'numérique' => [true, '123456'],
            'absent' => [false, 'kt9#vlp2!qwz8$mnx4'],
            'vide' => [false, ''],
        ];
    }

    #[DataProvider('defaultDictionaryProvider')]
    public function testDefaultDictionary(bool $expected, string $password): void
    {
        $this->assertSame($expected, (new FileDictionary())->contains($password));
    }

    public function testDefaultFilesAreLowercase(): void
    {
        foreach (FileDictionary::DEFAULT_FILES as $file) {
            $content = (string) file_get_contents($file);
            $this->assertSame(mb_strtolower($content), $content, $file);
        }
    }

    public function testCustomFileIsNormalized(): void
    {
        $file = (string) tempnam(sys_get_temp_dir(), 'dico');
        file_put_contents($file, "  Libriciel \n\nSoleil\n");

        try {
            $dictionary = new FileDictionary([$file]);
            $this->assertTrue($dictionary->contains('libriciel'));
            $this->assertTrue($dictionary->contains('soleil'));
            $this->assertFalse($dictionary->contains(''));
            $this->assertFalse($dictionary->contains('azerty'));
        } finally {
            unlink($file);
        }
    }

    public function testUnreadableFileThrowsException(): void
    {
        $dictionary = new FileDictionary(['/nonexistent/dictionary.txt']);

        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage('Impossible de lire le dictionnaire /nonexistent/dictionary.txt');
        $dictionary->contains('azerty');
    }
}
