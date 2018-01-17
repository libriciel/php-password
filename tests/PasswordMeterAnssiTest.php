<?php
namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\TestCase;
use Libriciel\Utility\Password\PasswordMeterAnssi;

class BasicsTest extends TestCase
{
    /**
     * Test de la méthode PasswordMeterAnssi::thresholds().
     */
    public function testThresholds()
    {
        $actual = PasswordMeterAnssi::thresholds();
        $expected = [
            128 => PasswordMeterAnssi::STRENGTH_VERY_STRONG,
            100 => PasswordMeterAnssi::STRENGTH_STRONG,
            80 => PasswordMeterAnssi::STRENGTH_MEDIUM,
            64 => PasswordMeterAnssi::STRENGTH_WEAK,
            0 => PasswordMeterAnssi::STRENGTH_REALLY_WEAK,
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la méthode PasswordMeterAnssi::symbols().
     */
    public function testSymbols()
    {
        // Binaire
        $this->assertEquals(2, PasswordMeterAnssi::symbols('0110'));
        // Décimal
        $this->assertEquals(10, PasswordMeterAnssi::symbols('0123'));
        // Hexadécimal
        $this->assertEquals(16, PasswordMeterAnssi::symbols('012F'));
        // Hexadécimal, majuscules et minuscules
        $this->assertEquals(22, PasswordMeterAnssi::symbols('012Ff'));
        // Alphabétique
        $this->assertEquals(26, PasswordMeterAnssi::symbols('azerty'));
        // Alphabétique, majuscules et minuscules
        $this->assertEquals(52, PasswordMeterAnssi::symbols('aZeRtY'));
        // Alphanumérique
        $this->assertEquals(36, PasswordMeterAnssi::symbols('az3rty'));
        // Alphanumérique, majuscules et minuscules
        $this->assertEquals(62, PasswordMeterAnssi::symbols('aZ3RtY'));
        // 70 symboles
        $this->assertEquals(70, PasswordMeterAnssi::symbols('aZ3Rt?'));
        // 90 symboles
        $this->assertEquals(90, PasswordMeterAnssi::symbols('aZ3Rt?&'));

//        // 8 symboles d'extra1
//        foreach(mb_str_split('€!#$*%? ') as $char) {
//            $this->assertEquals(8, PasswordMeterAnssi::symbols($char));
//        }
//
//        // 20 symboles d'extra2
//        foreach(mb_str_split('&[|]@^µ§:/,.,<>°²³\'"') as $char) {
//            $this->assertEquals(20, PasswordMeterAnssi::symbols($char));
//        }
    }
    
    /**
     * Test de la méthode PasswordMeterAnssi::entropyBits() avec des caractères
     * binaires.
     */
    public function testEntropyBitsBinary()
    {
        // 4 caractères
        $this->assertEquals(4, PasswordMeterAnssi::entropyBits('0110'));
        // 6 caractères
        $this->assertEquals(6, PasswordMeterAnssi::entropyBits('011001'));
        // 8 caractères
        $this->assertEquals(8, PasswordMeterAnssi::entropyBits('01100110'));
        // 10 caractères
        $this->assertEquals(10, PasswordMeterAnssi::entropyBits('0110011001'));
        // 12 caractères
        $this->assertEquals(12, PasswordMeterAnssi::entropyBits('011001100110'));
        // 16 caractères
        $this->assertEquals(16, PasswordMeterAnssi::entropyBits('0110011001101111'));
        // 20 caractères
        $this->assertEquals(20, PasswordMeterAnssi::entropyBits('01100110011011111001'));
        // 25 caractères
        $this->assertEquals(25, PasswordMeterAnssi::entropyBits('0110011001101111100110011'));
        // 30 caractères
        $this->assertEquals(30, PasswordMeterAnssi::entropyBits('011001100110111110011001101001'));
    }

    /**
     * Test de la méthode PasswordMeterAnssi::entropyBits() avec des caractères
     * décimaux.
     */
    public function testEntropyBitsDecimal()
    {
        // 4 caractères
        $this->assertEquals(13, PasswordMeterAnssi::entropyBits('0123'));
        // 6 caractères
        $this->assertEquals(20, PasswordMeterAnssi::entropyBits('012301'));
        // 8 caractères
        $this->assertEquals(27, PasswordMeterAnssi::entropyBits('01230123'));
    }

    /**
     * Test de la méthode PasswordMeterAnssi::entropyBits() avec des caractères
     * hexadécimaux.
     */
    public function testEntropyBitsHexadecimal()
    {
        // Majuscules
        $this->assertEquals(16, PasswordMeterAnssi::entropyBits('012F'));
        $this->assertEquals(32, PasswordMeterAnssi::entropyBits('012F012F'));

        // Minuscules
        $this->assertEquals(16, PasswordMeterAnssi::entropyBits('012f'));
        $this->assertEquals(32, PasswordMeterAnssi::entropyBits('012f012f'));

        // Majuscules et minuscules ?
    }

    /**
     * Test de la méthode PasswordMeterAnssi::entropyBits() avec des caractères
     * alphabétiques.
     */
    public function testEntropyBitsAlphabetical()
    {
        // Majuscules
        $this->assertEquals(19, PasswordMeterAnssi::entropyBits('ABCZ'));
        $this->assertEquals(38, PasswordMeterAnssi::entropyBits('ABCZABCZ'));

        // Minuscules
        $this->assertEquals(19, PasswordMeterAnssi::entropyBits('abcz'));
        $this->assertEquals(38, PasswordMeterAnssi::entropyBits('abczabcz'));

        // Majuscules et minuscules
        $this->assertEquals(23, PasswordMeterAnssi::entropyBits('AbCz'));
        $this->assertEquals(46, PasswordMeterAnssi::entropyBits('AbCzaBcZ'));
    }

    /**
     * Test de la méthode PasswordMeterAnssi::entropyBits() avec des caractères
     * alphanumériques.
     */
    public function testEntropyBitsAlphanumeric()
    {
        // Majuscules
        $this->assertEquals(21, PasswordMeterAnssi::entropyBits('01AZ'));
        $this->assertEquals(41, PasswordMeterAnssi::entropyBits('01AZ01AF'));
    }

    /**
     * Test de la méthode PasswordMeterAnssi::strength() avec des mots de passe
     * de force "très faible".
     */
    public function testStrengthReallyWeak()
    {
        // 9 caractères latin, majuscules et minuscules => 51 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_REALLY_WEAK,
            PasswordMeterAnssi::strength(str_repeat('Abz', 3))
        );
    }

    /**
     * Test de la méthode PasswordMeterAnssi::strength() avec des mots de passe
     * de force "faible".
     */
    public function testStrengthWeak()
    {
        // 16 caractères latin majuscules => 75 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_WEAK,
            PasswordMeterAnssi::strength(str_repeat('ABCZ', 4))
        );
    }

    /**
     * Test de la méthode PasswordMeterAnssi::strength() avec des mots de passe
     * de force "moyenne".
     */
    public function testStrengthMedium()
    {
        // 14 caractères latin, majuscules et minuscules => 80 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_MEDIUM,
            PasswordMeterAnssi::strength('AbCzaBcZAbCzaB')
        );

        // 16 caractères latin, majuscules et minuscules => 91 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_MEDIUM,
            PasswordMeterAnssi::strength('AbCzaBcZAbCzaBcZ')
        );
    }

    /**
     * Test de la méthode PasswordMeterAnssi::strength() avec des mots de passe
     * de force "forte".
     */
    public function testStrengthStrong()
    {
        // 30 caractères numériques => 100 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_STRONG,
            PasswordMeterAnssi::strength(str_repeat('0123456789', 3))
        );

        // 20 caractères latin, majuscules et minuscules => 114 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_STRONG,
            PasswordMeterAnssi::strength('AbCzaBcZAbCzaBcZaBcZ')
        );
    }

    /**
     * Test de la méthode PasswordMeterAnssi::strength() avec des mots de passe
     * de force "très forte".
     */
    public function testStrengthVeryStrong()
    {
        // 20 caractères des 70 possibles => 130 entropyBits
        $this->assertEquals(
            PasswordMeterAnssi::STRENGTH_VERY_STRONG,
            PasswordMeterAnssi::strength(str_repeat('aZ3?&', 4))
        );
    }
}
