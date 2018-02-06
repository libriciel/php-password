<?php
/**
 * Code source de la classe LibricielTest\Utility\Password\PasswordStrengthMeterAnssiTest
 *
 * @license CeCiLL V2
 */
namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\TestCase;
use Libriciel\Utility\Password\PasswordStrengthMeterAnssi;

/**
 * Tests unitaires de la classe Libriciel\Utility\Password\PasswordStrengthMeterAnssi
 */
class PasswordStrengthMeterAnssiTest extends TestCase
{
    /**
     * Test de la méthode PasswordStrengthMeterAnssi::thresholds().
     */
    public function testThresholds()
    {
        $actual = PasswordStrengthMeterAnssi::thresholds();
        $expected = [
            128 => PasswordStrengthMeterAnssi::STRENGTH_VERY_STRONG,
            100 => PasswordStrengthMeterAnssi::STRENGTH_STRONG,
            80 => PasswordStrengthMeterAnssi::STRENGTH_MEDIUM,
            64 => PasswordStrengthMeterAnssi::STRENGTH_WEAK,
            0 => PasswordStrengthMeterAnssi::STRENGTH_VERY_WEAK,
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols().
     */
    public function testSymbols()
    {
        // Binaire
        $this->assertEquals(2, PasswordStrengthMeterAnssi::symbols('0110'));
        // Décimal
        $this->assertEquals(10, PasswordStrengthMeterAnssi::symbols('0123'));
        // Hexadécimal
        $this->assertEquals(16, PasswordStrengthMeterAnssi::symbols('012F'));
        // Hexadécimal, majuscules et minuscules
        $this->assertEquals(22, PasswordStrengthMeterAnssi::symbols('012Ff'));
        // Alphabétique
        $this->assertEquals(26, PasswordStrengthMeterAnssi::symbols('azerty'));
        // Alphabétique, majuscules et minuscules
        $this->assertEquals(52, PasswordStrengthMeterAnssi::symbols('aZeRtY'));
        // Alphanumérique
        $this->assertEquals(36, PasswordStrengthMeterAnssi::symbols('az3rty'));
        // Alphanumérique, majuscules et minuscules
        $this->assertEquals(62, PasswordStrengthMeterAnssi::symbols('aZ3RtY'));
        // 70 symboles
        $this->assertEquals(70, PasswordStrengthMeterAnssi::symbols('aZ3Rt?'));
        // 90 symboles
        $this->assertEquals(90, PasswordStrengthMeterAnssi::symbols('aZ3Rt?&'));
    }
    

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols() avec des
     * caractères français accentués.
     */
    public function testSymbolsFrenchAccents()
    {
        $this->assertEquals(42, PasswordStrengthMeterAnssi::symbols('été'));
        $this->assertEquals(42, PasswordStrengthMeterAnssi::symbols('GÜDRUN'));
    }
    
    /**
     * Test de la méthode PasswordStrengthMeterAnssi::symbols() avec l'entropie
     * par symboles définie dans le tableau sur wikipedia.
     *
     * @see {@url https://en.wikipedia.org/wiki/Password_strength#Random_passwords}
     */
    public function testSymbolsWikipedia()
    {
        $this->assertEquals(10, PasswordStrengthMeterAnssi::symbols('2'));
        $this->assertEquals(16, PasswordStrengthMeterAnssi::symbols('F'));
        $this->assertEquals(26, PasswordStrengthMeterAnssi::symbols('G'));
        $this->assertEquals(36, PasswordStrengthMeterAnssi::symbols('3G'));
        $this->assertEquals(52, PasswordStrengthMeterAnssi::symbols('gG'));
        $this->assertEquals(62, PasswordStrengthMeterAnssi::symbols('2gG'));
    }
    
    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec l'entropie
     * par symboles définie dans le tableau sur wikipedia.
     *
     * @see {@url https://en.wikipedia.org/wiki/Password_strength#Random_passwords}
     */
    public function testEntropyWikipedia()
    {
        $this->assertEquals(3.322, PasswordStrengthMeterAnssi::entropy('2', 3));
        $this->assertEquals(4.000, PasswordStrengthMeterAnssi::entropy('F', 3));
        $this->assertEquals(4.700, PasswordStrengthMeterAnssi::entropy('G', 3));
        $this->assertEquals(10.34, PasswordStrengthMeterAnssi::entropy('3G', 3));
        $this->assertEquals(11.401, PasswordStrengthMeterAnssi::entropy('gG', 3));
        $this->assertEquals(17.863, PasswordStrengthMeterAnssi::entropy('2gG', 3));
    }
    
    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des caractères
     * binaires.
     */
    public function testEntropyBinary()
    {
        // 4 caractères
        $this->assertEquals(4, PasswordStrengthMeterAnssi::entropy('0110'));
        // 6 caractères
        $this->assertEquals(6, PasswordStrengthMeterAnssi::entropy('011001'));
        // 8 caractères
        $this->assertEquals(8, PasswordStrengthMeterAnssi::entropy('01100110'));
        // 10 caractères
        $this->assertEquals(10, PasswordStrengthMeterAnssi::entropy('0110011001'));
        // 12 caractères
        $this->assertEquals(12, PasswordStrengthMeterAnssi::entropy('011001100110'));
        // 16 caractères
        $this->assertEquals(16, PasswordStrengthMeterAnssi::entropy('0110011001101111'));
        // 20 caractères
        $this->assertEquals(20, PasswordStrengthMeterAnssi::entropy('01100110011011111001'));
        // 25 caractères
        $this->assertEquals(25, PasswordStrengthMeterAnssi::entropy('0110011001101111100110011'));
        // 30 caractères
        $this->assertEquals(30, PasswordStrengthMeterAnssi::entropy('011001100110111110011001101001'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des caractères
     * décimaux.
     */
    public function testEntropyDecimal()
    {
        // 4 caractères
        $this->assertEquals(13, PasswordStrengthMeterAnssi::entropy('0123'));
        // 6 caractères
        $this->assertEquals(20, PasswordStrengthMeterAnssi::entropy('012301'));
        // 8 caractères
        $this->assertEquals(27, PasswordStrengthMeterAnssi::entropy('01230123'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des caractères
     * hexadécimaux.
     */
    public function testEntropyHexadecimal()
    {
        // Majuscules
        $this->assertEquals(16, PasswordStrengthMeterAnssi::entropy('012F'));
        $this->assertEquals(32, PasswordStrengthMeterAnssi::entropy('012F012F'));

        // Minuscules
        $this->assertEquals(16, PasswordStrengthMeterAnssi::entropy('012f'));
        $this->assertEquals(32, PasswordStrengthMeterAnssi::entropy('012f012f'));

        // Majuscules et minuscules ?
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des caractères
     * alphabétiques.
     */
    public function testEntropyAlphabetical()
    {
        // Majuscules
        $this->assertEquals(19, PasswordStrengthMeterAnssi::entropy('ABCZ'));
        $this->assertEquals(38, PasswordStrengthMeterAnssi::entropy('ABCZABCZ'));

        // Minuscules
        $this->assertEquals(19, PasswordStrengthMeterAnssi::entropy('abcz'));
        $this->assertEquals(38, PasswordStrengthMeterAnssi::entropy('abczabcz'));

        // Majuscules et minuscules
        $this->assertEquals(23, PasswordStrengthMeterAnssi::entropy('AbCz'));
        $this->assertEquals(46, PasswordStrengthMeterAnssi::entropy('AbCzaBcZ'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des caractères
     * alphanumériques.
     */
    public function testEntropyAlphanumeric()
    {
        // Majuscules
        $this->assertEquals(21, PasswordStrengthMeterAnssi::entropy('01AZ'));
        $this->assertEquals(41, PasswordStrengthMeterAnssi::entropy('01AZ01AF'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des
     * caractères français accentués.
     */
    public function testEntropyFrenchAccents()
    {
        $this->assertEquals(16, PasswordStrengthMeterAnssi::entropy('été'));
        $this->assertEquals(32, PasswordStrengthMeterAnssi::entropy('GÜDRUN'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::entropy() avec des
     * exemples concrêts.
     */
    public function testEntropyExemples()
    {
        // @see {@url https://www.ssi.gouv.fr/guide/mot-de-passe/}
        $this->assertEquals(67, PasswordStrengthMeterAnssi::entropy('ght8CD%E7am'));
        $this->assertEquals(60, PasswordStrengthMeterAnssi::entropy('1tvmQ2tl’A'));
        $this->assertEquals(64, PasswordStrengthMeterAnssi::entropy('1tvmQ2tl\'A'));

        // @see {@url https://www.explainxkcd.com/wiki/index.php/936:_Password_Strength}
        $this->assertEquals(70, PasswordStrengthMeterAnssi::entropy('Tr0ub4dor&3'));
        $this->assertEquals(118, PasswordStrengthMeterAnssi::entropy('correcthorsebatterystaple'));
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() avec des mots de passe
     * de force "très faible".
     */
    public function testStrengthVeryWeak()
    {
        // 9 caractères latin, majuscules et minuscules => 51 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_VERY_WEAK,
            PasswordStrengthMeterAnssi::strength(str_repeat('Abz', 3))
        );
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() avec des mots de passe
     * de force "faible".
     */
    public function testStrengthWeak()
    {
        // 16 caractères latin majuscules => 75 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_WEAK,
            PasswordStrengthMeterAnssi::strength(str_repeat('ABCZ', 4))
        );
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() avec des mots de passe
     * de force "moyenne".
     */
    public function testStrengthMedium()
    {
        // 14 caractères latin, majuscules et minuscules => 80 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_MEDIUM,
            PasswordStrengthMeterAnssi::strength('AbCzaBcZAbCzaB')
        );

        // 16 caractères latin, majuscules et minuscules => 91 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_MEDIUM,
            PasswordStrengthMeterAnssi::strength('AbCzaBcZAbCzaBcZ')
        );
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() avec des mots de passe
     * de force "forte".
     */
    public function testStrengthStrong()
    {
        // 30 caractères numériques => 100 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_STRONG,
            PasswordStrengthMeterAnssi::strength(str_repeat('0123456789', 3))
        );

        // 20 caractères latin, majuscules et minuscules => 114 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_STRONG,
            PasswordStrengthMeterAnssi::strength('AbCzaBcZAbCzaBcZaBcZ')
        );
    }

    /**
     * Test de la méthode PasswordStrengthMeterAnssi::strength() avec des mots de passe
     * de force "très forte".
     */
    public function testStrengthVeryStrong()
    {
        // 20 caractères des 70 possibles => 130 entropy
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_VERY_STRONG,
            PasswordStrengthMeterAnssi::strength(str_repeat('aZ3?&', 4))
        );

        // 25 caractères des 62 possibles
        // Valeurs par défaut utilisées par PasswordGeneratorAnssi::generate
        $password = '3357zPqv9p45PS4CXyaZPOyy5';
        $this->assertEquals(
            PasswordStrengthMeterAnssi::STRENGTH_VERY_STRONG,
            PasswordStrengthMeterAnssi::strength($password)
        );
    }
    
    /**
     * Tests de l'entropie et de la force des mots de passes du fichier README.md.
     * @todo: tests aux limites
     */
    public function testExamplePasswords()
    {
        $false = [
            'binary'  => false,
            'numbers'  => false,
            'lowercase_hexadecimal' => false,
            'uppercase_hexadecimal' => false,
            'lowercase_letters' => false,
            'uppercase_letters' => false,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false
        ];
        $classes = [
//            2 => ['binary' => true] + $false,
//            10 => ['numbers' => true] + $false,
//            16 => ['numbers'  => true, 'uppercase_hexadecimal' => true] + $false,
//            26 => ['uppercase_letters' => true] + $false,
//            36 => ['uppercase_letters' => true, 'numbers'  => true] + $false,
//            52 => ['uppercase_letters' => true, 'lowercase_letters'  => true] + $false,
//            62 => ['uppercase_letters' => true, 'lowercase_letters'  => true, 'numbers'  => true] + $false,
//            70 => ['uppercase_letters' => true, 'lowercase_letters'  => true, 'numbers'  => true, 'symbols_1' => true] + $false,
            90 => ['uppercase_letters' => true, 'lowercase_letters'  => true, 'numbers'  => true, 'symbols_1' => true, 'symbols_2' => true] + $false,
        ];

        $thresholds = array_reverse(array_keys(\Libriciel\Utility\Password\PasswordStrengthMeterAnssi::thresholds()));
        $thresholds = [64, 80];
        $exemples = [];
        foreach($classes as $symbols => $params) {
            $exemples[$symbols] = [];
            foreach($thresholds as $entropy) {
                $length = max(5, ceil($entropy / log($symbols, 2)));
                $password = \Libriciel\Utility\Password\PasswordGeneratorAnssi::generate($length, $params);

                $exemples[$symbols][$password] = [
                    'length' => mb_strlen($password),
                    'symbols' => PasswordStrengthMeterAnssi::symbols($password),
                    'entropy' => PasswordStrengthMeterAnssi::entropy($password),
                    'strength' => PasswordStrengthMeterAnssi::strength($password)
                ];
            }
        }
echo "\n".var_export($exemples, true)."\n";

        //----------------------------------------------------------------------
        $exemples = [
            '0A1B2C3D4E5F6A7B8C9D10E11' => [
                'length' => 25,
                'symbols' => 16,
                'entropy' => 100,
                'strength' => PasswordStrengthMeterAnssi::STRENGTH_STRONG
            ],
        ];
        
        foreach ($exemples as $password => $expected) {
            $actual = [
                'length' => mb_strlen($password),
                'symbols' => PasswordStrengthMeterAnssi::symbols($password),
                'entropy' => PasswordStrengthMeterAnssi::entropy($password),
                'strength' => PasswordStrengthMeterAnssi::strength($password)
            ];

            $message = sprintf('(mot de passe: %s)', $password);
            $this->assertEquals($expected, $actual, $message);
        }
    }
}
