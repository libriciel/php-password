<?php

declare(strict_types=1);

namespace LibricielTest\Password\Service;

use Libriciel\Password\Service\PasswordGeneratorAnssi;
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Libriciel\Password\Service\PasswordStrengthMeterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\Service\PasswordStrengthMeterAnssi.
 */
class PasswordStrengthMeterAnssiTest extends TestCase
{
    /**
     * Test de la méthode PasswordStrengthMeterAnssi::thresholds().
     */
    public function testThresholds(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $actual = $pwdStrengthMeter->thresholds();
        $expected = [
            128 => PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG,
            100 => PasswordStrengthMeterInterface::STRENGTH_STRONG,
            80 => PasswordStrengthMeterInterface::STRENGTH_MEDIUM,
            64 => PasswordStrengthMeterInterface::STRENGTH_WEAK,
            0 => PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK,
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Pour les tests Wikipédia :
     *
     * @return array<string, array{int, string}>
     *
     * @see {@url https://en.wikipedia.org/wiki/Password_strength#Random_passwords}
     */
    public static function symbolsProvider(): array
    {
        return [
            'binary' => [2, '0110'],
            'decimal' => [10, '0123'],
            'hexadecimal' => [16, '012F'],
            'hexadecimal with upper and lower case' => [22, '012Ff'],
            'alphabet' => [26, 'azerty'],
            'alphabet with upper and lower case' => [52, 'aZeRtY'],
            'alphanumeric' => [36, 'az3rty'],
            'alphanumeric with upper and lower case' => [62, 'aZ3RtY'],
            '70 symbols' => [70, 'aZ3Rt?'],
            '90 symbols' => [90, 'aZ3Rt?&'],
            'french diacritic' => [42, 'été'],
            'french diacritic upper case' => [42, 'GÜDRUN'],
            'Wikipedia test 1' => [10, '2'],
            'Wikipedia test 2' => [16, 'F'],
            'Wikipedia test 3' => [26, 'G'],
            'Wikipedia test 4' => [36, '3G'],
            'Wikipedia test 5' => [52, 'gG'],
            'Wikipedia test 6' => [62, '2gG'],
        ];
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols().
     */
    #[DataProvider('symbolsProvider')]
    public function testSymbols(int $expectedSymbols, string $input): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $this->assertEquals(
            $expectedSymbols,
            $pwdStrengthMeter->symbols($input)
        );
    }

    /**
     * Pour Wikipédia :
     *
     * @return array<string, array{0: float|int, 1: string, 2?: int}>
     *
     * @see {@url https://en.wikipedia.org/wiki/Password_strength#Random_passwords}
     */
    public static function entropyProvider(): array
    {
        return [
            'Wikipedia test 1' => [3.322, '2', 3],
            'Wikipedia test 2' => [4.000, 'F', 3],
            'Wikipedia test 3' => [4.700, 'G', 3],
            'Wikipedia test 4' => [10.34, '3G', 3],
            'Wikipedia test 5' => [11.401, 'gG', 3],
            'Wikipedia test 6' => [17.863, '2gG', 3],
            'binary 4' => [4, '0110'],
            'binary 6' => [6, '011001'],
            'binary 8' => [8, '01100110'],
            'binary 10' => [10, '0110011001'],
            'binary 12' => [12, '011001100110'],
            'binary 16' => [16, '0110011001101111'],
            'binary 20' => [20, '01100110011011111001'],
            'binary 25' => [25, '0110011001101111100110011'],
            'binary 30' => [30, '011001100110111110011001101001'],
            'decimal 4' => [13, '0123'],
            'decimal 6' => [20, '012301'],
            'decimal 8' => [27, '01230123'],
            'hexadecimal 4' => [16, '012F'],
            'hexadecimal 8' => [32, '012F012F'],
            'hexadecimal 4 with lower case' => [16, '012f'],
            'hexadecimal 8 with lower case' => [32, '012f012f'],
            'alphabet 4 upper' => [19, 'ABCZ'],
            'alphabet 8 upper' => [38, 'ABCZABCZ'],
            'alphabet 4 lower' => [19, 'abcz'],
            'alphabet 8 lower' => [38, 'abczabcz'],
            'alphabet 4 upper and lower' => [23, 'AbCz'],
            'alphabet 8 upper and lower' => [46, 'AbCzaBcZ'],
            'alphanumeric 4' => [21, '01AZ'],
            'alphanumeric 8' => [41, '01AZ01AF'],
            'french diacritic lower 3' => [16, 'été'],
            'french diacritic upper 6' => [32, 'GÜDRUN'],
            'precision 0' => [61, 'mot de passe'],
            'precision 1' => [61.0, 'mot de passe', 1],
            'precision 2' => [61.05, 'mot de passe', 2],
            'precision 3' => [61.050, 'mot de passe', 3],
            'precision 4' => [61.0496, 'mot de passe', 4],
            // @see {@url https://www.ssi.gouv.fr/guide/mot-de-passe/}
            'ssi 1' => [67, 'ght8CD%E7am'],
            'ssi 2' => [60, '1tvmQ2tl’A'],
            'ssi 3' => [64, '1tvmQ2tl\'A'],
            // @see {@url https://www.explainxkcd.com/wiki/index.php/936:_Password_Strength}
            'xkcd 1' => [70, 'Tr0ub4dor&3'],
            'xkcd 2' => [118, 'correcthorsebatterystaple'],
        ];
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec l'entropie
     * par symboles définie dans le tableau sur wikipedia.
     */
    #[DataProvider('entropyProvider')]
    public function testEntropy(float $expectedEntropy, string $input, int $precision = 0): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $this->assertEquals($expectedEntropy, $pwdStrengthMeter->entropy($input, $precision));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function strengthProvider(): array
    {
        return [
            'very weak' => [PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK, str_repeat('Abz', 3)],
            'weak' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('ABCZ', 4)],
            'medium 1' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, 'AbCzaBcZAbCzaBc'],
            'medium 2' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, 'AbCzaBcZAbCzaBcZ'],
            'strong 1' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, str_repeat('0123456789', 3).'01234'],
            'strong 2' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, 'AbCzaBcZAbCzaBcZaBcZ'],
            'very strong 1' => [
                PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG,
                str_repeat('aZ3?&', 4),
            ],
            'very strong 2' => [PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG, '3357zPqv9p45PS4CXyaZPOyy5'],
        ];
    }

    #[DataProvider('strengthProvider')]
    public function testStrength(int $expectedStrength, string $input): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $this->assertEquals(
            $expectedStrength,
            $pwdStrengthMeter->strength($input)
        );
    }

    /**
     * Test des constantes de PasswordStrengthMeterInterface et de son
     * implémentation.
     */
    public function testImplementsInterface(): void
    {
        // Contrat d'API vérifié à l'exécution, garanti statiquement
        $this->assertInstanceOf(PasswordStrengthMeterInterface::class, new PasswordStrengthMeterAnssi()); // @phpstan-ignore method.alreadyNarrowedType

        $this->assertSame(1, PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame(2, PasswordStrengthMeterInterface::STRENGTH_WEAK); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame(3, PasswordStrengthMeterInterface::STRENGTH_MEDIUM); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame(4, PasswordStrengthMeterInterface::STRENGTH_STRONG); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame(5, PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Les seuils sont retournés en ordre descendant.
     */
    public function testThresholdsOrder(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame([128, 100, 80, 64, 0], array_keys($pwdStrengthMeter->thresholds()));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function specialCharactersProvider(): array
    {
        $classes = [
            8 => '€!#$*% ?',
            20 => '&[|]@^µ§\\:/;.,<>°²³\'"',
            16 => 'àâäæçèéêëîïôœùûüÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ',
        ];

        $result = [];
        foreach ($classes as $symbols => $characters) {
            foreach (mb_str_split($characters) as $char) {
                $result[sprintf('%d symboles: "%s"', $symbols, $char)] = [$symbols, $char];
            }
        }

        return $result;
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols() pour chacun des
     * caractères spéciaux pris isolément.
     */
    #[DataProvider('specialCharactersProvider')]
    public function testSymbolsSpecialCharacters(int $expectedSymbols, string $input): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame($expectedSymbols, $pwdStrengthMeter->symbols($input));
    }

    /**
     * Chaque caractère que le générateur peut produire est reconnu par
     * symbols() (#5).
     */
    public function testSymbolsRecognizesEveryGeneratedCharacter(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $pwdGenerator = new PasswordGeneratorAnssi();
        $allClasses = array_fill_keys(array_keys($pwdGenerator->defaults()), true);
        $characters = $pwdGenerator->characters($allClasses);

        foreach ($characters as $char) {
            $this->assertGreaterThan(
                0,
                $pwdStrengthMeter->symbols($char),
                sprintf('Le caractère "%s" n\'est pas reconnu', $char)
            );
        }
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function symbolsCombinationsProvider(): array
    {
        return [
            'empty string' => [0, ''],
            'binary single character' => [2, '1'],
            'binary takes precedence over decimal' => [2, '0101'],
            'hexadecimal lower case letters only' => [16, 'cafe'],
            'hexadecimal takes precedence over alphabet' => [16, 'abc'],
            'hexadecimal with digit' => [16, '0a'],
            'hexadecimal mixed case' => [22, 'Cafe'],
            'not hexadecimal' => [26, 'abcg'],
            'decimal and lower case diacritic' => [26, '0é'],
            'lower and upper case diacritic' => [32, 'éÉ'],
            'symbols 1 and symbols 2' => [28, '!&'],
            'all classes' => [122, '0aA€&éÉ'],
        ];
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols() avec des
     * combinaisons de classes de caractères.
     */
    #[DataProvider('symbolsCombinationsProvider')]
    public function testSymbolsCombinations(int $expectedSymbols, string $input): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame($expectedSymbols, $pwdStrengthMeter->symbols($input));
    }

    public function testEntropyReturnsFloat(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame(61.0, $pwdStrengthMeter->entropy('mot de passe'));
    }

    public function testEntropyWithNegativePrecision(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame(60.0, $pwdStrengthMeter->entropy('mot de passe', -1));
    }

    /**
     * La longueur est comptée en caractères et non en octets.
     */
    public function testEntropyMultibyte(): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        // 4 caractères, 16 symboles
        $this->assertSame(16.0, $pwdStrengthMeter->entropy('ÀÉÈÇ'));
        // 4 caractères, 8 symboles
        $this->assertSame(12.0, $pwdStrengthMeter->entropy('€€€€'));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function strengthThresholdsProvider(): array
    {
        return [
            'empty string' => [PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK, ''],
            '63 bits' => [PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK, str_repeat('0', 63)],
            '64 bits' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('0', 64)],
            '79 bits' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('0', 79)],
            '80 bits' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, str_repeat('0', 80)],
            '99 bits' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, str_repeat('0', 99)],
            '100 bits' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, str_repeat('0', 100)],
            '127 bits' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, str_repeat('0', 127)],
            '128 bits' => [PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG, str_repeat('0', 128)],
            '1024 bits' => [PasswordStrengthMeterInterface::STRENGTH_VERY_STRONG, str_repeat('0', 1024)],
            // L'entropie n'est pas arrondie avant d'être comparée aux seuils (#10)
            // 13 × log2(30) = 63,79
            'just below 64 bits' => [PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK, str_repeat('3', 12).'&'],
            // 24 × log2(10) = 79,73
            'just below 80 bits, decimal' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('3', 24)],
            // 17 × log2(26) = 79,91
            'just below 80 bits, alphabet' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('g', 17)],
            // 30 × log2(10) = 99,66
            'just below 100 bits' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, str_repeat('3', 30)],
            // 19 × log2(106) = 127,83
            'just below 128 bits' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, 'aA0&?à'.str_repeat('a', 13)],
        ];
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() aux bornes des
     * seuils : l'entropie, non arrondie, est comparée aux seuils, bornes incluses.
     */
    #[DataProvider('strengthThresholdsProvider')]
    public function testStrengthThresholds(int $expectedStrength, string $input): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        $this->assertSame($expectedStrength, $pwdStrengthMeter->strength($input));
    }
}
