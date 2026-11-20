<?php

declare(strict_types=1);

namespace LibricielTest\Password\Service;

use Libriciel\Password\Dictionary\PasswordDictionaryInterface;
use Libriciel\Password\PasswordException;
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Libriciel\Password\Service\PasswordValidatorAnssi;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\Service\PasswordValidatorAnssi.
 */
class PasswordValidatorAnssiTest extends TestCase
{
    private const array PERSONAL_DATA = ['Jérôme', 'Dupont', 'jerome.dupont@exemple.fr', '12/03/1985'];

    /**
     * Validateur avec un petit dictionnaire, pour des tests indépendants des
     * listes livrées.
     */
    private function validator(int $minClasses = 3, int $passphraseLength = 20, int $patternLength = 4): PasswordValidatorAnssi
    {
        $dictionary = new class implements PasswordDictionaryInterface {
            public function contains(string $password): bool
            {
                return in_array($password, ['motdepasse', 'password', 'soleil', 'marseille', 'liberté', 'dupont'], true);
            }
        };

        return new PasswordValidatorAnssi($dictionary, $minClasses, $passphraseLength, $patternLength);
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function violationsProvider(): array
    {
        return [
            'valide' => [[], 'kT9#vLp2!qWz8$mNx4'],
            'phrase de passe valide' => [[], 'le chat dort sur le canapé vert'],
            'entropie' => [[PasswordValidatorAnssi::VIOLATION_ENTROPY], 'kT9#vLp2!q'],
            'complexité, juste sous la phrase de passe' => [[PasswordValidatorAnssi::VIOLATION_COMPLEXITY], 'ktvlpqwzmnxbrhgsjdy'],
            'phrase de passe sans complexité' => [[], 'ktvlpqwzmnxbrhgsjdyc'],
            'suite de lettres' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qWz8$mNx4Abcd'],
            'suite de chiffres décroissante' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qWz8$mNx4321'],
            'ligne de clavier azerty' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qWz8$mNx4Azer'],
            'ligne de clavier qwerty à l\'envers' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qWz8$mNx4lkjh'],
            'caractère répété' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qWz8$mNx4!!!!'],
            'bloc répété' => [[PasswordValidatorAnssi::VIOLATION_PATTERN], 'kT9#vLp2!qkT9#vLp2!q'],
            'dictionnaire' => [[PasswordValidatorAnssi::VIOLATION_ENTROPY, PasswordValidatorAnssi::VIOLATION_COMPLEXITY, PasswordValidatorAnssi::VIOLATION_DICTIONARY], 'MotDePasse'],
            'dictionnaire avec accent' => [[PasswordValidatorAnssi::VIOLATION_ENTROPY, PasswordValidatorAnssi::VIOLATION_COMPLEXITY, PasswordValidatorAnssi::VIOLATION_DICTIONARY], 'Liberté'],
            'dictionnaire en leet speak' => [[PasswordValidatorAnssi::VIOLATION_ENTROPY, PasswordValidatorAnssi::VIOLATION_DICTIONARY], 'M0tD3P@$$e'],
            'dictionnaire en leet speak, 1 pour l' => [[PasswordValidatorAnssi::VIOLATION_ENTROPY, PasswordValidatorAnssi::VIOLATION_DICTIONARY], 'S0le1l'],
            'dictionnaire avec chiffres et symboles autour' => [[PasswordValidatorAnssi::VIOLATION_DICTIONARY], '#2024Marseille!'],
            'partie centrale trop courte' => [[], '9k#!x9k#!x0p€w%2§T'],
            'nom' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!qDUPONT'],
            'prénom sans accent' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!qjerome'],
            'prénom en leet speak' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!qJ3r0m€'],
            'domaine de l\'e-mail' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!qexemple'],
            'année de naissance' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!q1985'],
            'date de naissance sans séparateurs' => [[PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA], 'kT9#vLp2!q12031985'],
            'tout' => [
                [
                    PasswordValidatorAnssi::VIOLATION_ENTROPY,
                    PasswordValidatorAnssi::VIOLATION_COMPLEXITY,
                    PasswordValidatorAnssi::VIOLATION_PATTERN,
                    PasswordValidatorAnssi::VIOLATION_DICTIONARY,
                    PasswordValidatorAnssi::VIOLATION_PERSONAL_DATA,
                ],
                'dupont1234',
            ],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('violationsProvider')]
    public function testViolations(array $expected, string $password): void
    {
        $validator = $this->validator();
        $this->assertSame($expected, $validator->violations($password, 80, self::PERSONAL_DATA));
        $this->assertSame([] === $expected, $validator->validPassword($password, 80, self::PERSONAL_DATA));
    }

    /**
     * Chaque contrôle unitaire donne le même résultat que violations().
     *
     * @param list<string> $expected
     */
    #[DataProvider('violationsProvider')]
    public function testSingleChecksMatchViolations(array $expected, string $password): void
    {
        $validator = $this->validator();
        $this->assertSame(!in_array(PasswordValidatorAnssi::VIOLATION_COMPLEXITY, $expected, true), $validator->validComplexity($password));
        $this->assertSame(!in_array(PasswordValidatorAnssi::VIOLATION_PATTERN, $expected, true), $validator->validPattern($password));
        $this->assertSame(!in_array(PasswordValidatorAnssi::VIOLATION_DICTIONARY, $expected, true), $validator->validDictionary($password));
    }

    /**
     * Les contrôles unitaires ne dépendent pas de l'entropie : un mot de
     * passe court peut les réussir.
     */
    public function testSingleChecksIgnoreEntropy(): void
    {
        $validator = $this->validator();
        $this->assertFalse($validator->validComplexity('kdjtmzpqrw'));
        $this->assertTrue($validator->validComplexity('kdjT9mzpqr'));
        $this->assertTrue($validator->validPattern('kdjT9mzpqr'));
        $this->assertFalse($validator->validPattern('Azerty'));
        $this->assertTrue($validator->validDictionary('kdjT9mzpqr'));
        $this->assertFalse($validator->validDictionary('Soleil2024!'));
    }

    /**
     * Une partie centrale de moins de 4 lettres n'est pas comparée au
     * dictionnaire : un mot de passe aléatoire ne doit pas être refusé parce
     * qu'il n'en reste que quelques lettres.
     */
    public function testShortCoreIsNotComparedToDictionary(): void
    {
        $dictionary = new class implements PasswordDictionaryInterface {
            public function contains(string $password): bool
            {
                return in_array($password, ['sol', 'sole'], true);
            }
        };
        $validator = new PasswordValidatorAnssi($dictionary);

        $this->assertTrue($validator->validDictionary('48!Sol#92'));
        $this->assertFalse($validator->validDictionary('48!Sole#92'));
        $this->assertFalse($validator->validDictionary('Sol'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function shorterThanPatternProvider(): array
    {
        return [
            'vide' => [''],
            'un caractère' => ['a'],
            'trois caractères' => ['abc'],
        ];
    }

    /**
     * Un mot de passe plus court que la longueur des motifs ne contient pas de
     * suite, sauf s'il est formé d'un bloc répété.
     */
    #[DataProvider('shorterThanPatternProvider')]
    public function testPasswordShorterThanPattern(string $password): void
    {
        $this->assertTrue($this->validator()->validPattern($password));
        $this->assertFalse($this->validator()->validPattern('aa'));
    }

    public function testEmptyPassword(): void
    {
        $this->assertSame(
            [PasswordValidatorAnssi::VIOLATION_ENTROPY, PasswordValidatorAnssi::VIOLATION_COMPLEXITY],
            $this->validator()->violations('', 1, self::PERSONAL_DATA)
        );
        $this->assertFalse($this->validator()->validPassword('', 0, []));
    }

    /**
     * Le calcul d'entropie peut être fourni par l'application.
     */
    public function testInjectedStrengthMeter(): void
    {
        $meter = $this->createMock(PasswordStrengthMeterAnssi::class);
        $meter->expects($this->once())->method('entropy')->with('kT9#vLp2!q', 10)->willReturn(200.0);

        $validator = new PasswordValidatorAnssi(meter: $meter);
        $this->assertSame([], $validator->violations('kT9#vLp2!q', 128));
    }

    public function testEntropyThreshold(): void
    {
        $validator = $this->validator();
        // 18 caractères parmi 70 symboles : 110,33 bits
        $this->assertTrue($validator->validPassword('kT9#vLp2!qWz8$mNx4', 110.3));
        $this->assertFalse($validator->validPassword('kT9#vLp2!qWz8$mNx4', 110.4));
    }

    public function testWithoutPersonalData(): void
    {
        $this->assertTrue($this->validator()->validPassword('kT9#vLp2!qDUPONT', 80));
    }

    public function testShortPersonalDataIsIgnored(): void
    {
        $this->assertTrue($this->validator()->validPassword('kT9#vLp2!qWz8$mNx4', 80, ['Lp', 'x4', '']));
    }

    public function testMinClasses(): void
    {
        $this->assertSame([], $this->validator(minClasses: 1)->violations('ktvlpqwzmnxbrhgsjdyc', 80));
        $this->assertSame(
            [PasswordValidatorAnssi::VIOLATION_COMPLEXITY],
            $this->validator(minClasses: 4)->violations('kT9vLp2qWz8mNx4bRh6', 80)
        );
    }

    public function testPassphraseLength(): void
    {
        $this->assertSame([], $this->validator(passphraseLength: 16)->violations('ktvlpqwzmnxbrhgs', 70));
    }

    public function testPatternLength(): void
    {
        $this->assertSame([], $this->validator(patternLength: 5)->violations('kT9#vLp2!qWz8$mNx4Abcd', 80));
        $this->assertSame(
            [PasswordValidatorAnssi::VIOLATION_PATTERN],
            $this->validator(patternLength: 3)->violations('kT9#vLp2!qWz8$mNx4Abc', 80)
        );
    }

    /**
     * Le dictionnaire livré par défaut est bien utilisé.
     */
    public function testDefaultDictionary(): void
    {
        $validator = new PasswordValidatorAnssi();
        // 85,8 bits : l'entropie seule ne suffit pas à le rejeter
        $this->assertSame([PasswordValidatorAnssi::VIOLATION_DICTIONARY], $validator->violations('Marseille2024!', 80));
        $this->assertSame([PasswordValidatorAnssi::VIOLATION_DICTIONARY], $validator->violations('Motdepasse2024!#', 80));
        // Prénom de l'INSEE, déguisé
        $this->assertSame([PasswordValidatorAnssi::VIOLATION_DICTIONARY], $validator->violations('#1985M@thilde!!', 80));
        $this->assertTrue($validator->validPassword('kT9#vLp2!qWz8$mNx4', 100));
    }

    /**
     * @return array<string, array{int, int, int, string}>
     */
    public static function invalidOptionsProvider(): array
    {
        return [
            'classes négatives' => [-1, 20, 4, 'Le nombre de classes exigées doit être compris entre 0 et 4 (-1)'],
            'trop de classes' => [5, 20, 4, 'Le nombre de classes exigées doit être compris entre 0 et 4 (5)'],
            'phrase de passe' => [3, 0, 4, 'Longueurs invalides : phrase de passe 0 (minimum 1), motif 4 (minimum 2)'],
            'motif' => [3, 20, 1, 'Longueurs invalides : phrase de passe 20 (minimum 1), motif 1 (minimum 2)'],
        ];
    }

    #[DataProvider('invalidOptionsProvider')]
    public function testInvalidOptionsThrowException(int $minClasses, int $passphraseLength, int $patternLength, string $message): void
    {
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage($message);
        new PasswordValidatorAnssi(null, $minClasses, $passphraseLength, $patternLength);
    }
}
