<?php

declare(strict_types=1);

namespace LibricielTest\Password\Service;

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
    public function testThresholds()
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
    public function testSymbols(int $expectedSymbols, string $input)
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
    public function testEntropy(float $expectedEntropy, string $input, int $precision = 0)
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();
        $this->assertEquals($expectedEntropy, $pwdStrengthMeter->entropy($input, $precision));
    }

    public static function strengthProvider(): array
    {
        return [
            'very weak' => [PasswordStrengthMeterInterface::STRENGTH_VERY_WEAK, str_repeat('Abz', 3)],
            'weak' => [PasswordStrengthMeterInterface::STRENGTH_WEAK, str_repeat('ABCZ', 4)],
            'medium 1' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, 'AbCzaBcZAbCzaB'],
            'medium 2' => [PasswordStrengthMeterInterface::STRENGTH_MEDIUM, 'AbCzaBcZAbCzaBcZ'],
            'strong 1' => [PasswordStrengthMeterInterface::STRENGTH_STRONG, str_repeat('0123456789', 3)],
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
}
