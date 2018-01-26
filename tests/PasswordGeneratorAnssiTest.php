<?php
/**
 * Code source de la classe LibricielTest\Utility\Password\PasswordGeneratorAnssiTest
 *
 * @license CeCiLL V2
 */
namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\TestCase;
use Libriciel\Utility\Password\PasswordGeneratorAnssi;
use Libriciel\Utility\Password\PasswordStrengthMeterAnssi;

/**
 * Tests unitaires de la classe Libriciel\Utility\Password\PasswordGeneratorAnssi
 */
class PasswordGeneratorAnssiTest extends TestCase
{
    /**
     * Test de la méthode PasswordGeneratorAnssi::defaults().
     */
    public function testDefaults()
    {
        $actual = PasswordGeneratorAnssi::defaults();
        $expected = [
            'binary' => true,
            'numbers' => true,
            'lowercase_hexadecimal' => true,
            'uppercase_hexadecimal' => true,
            'lowercase_letters' => true,
            'uppercase_letters' => true,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate() au moyen de la méthode
     * PasswordStrengthMeterAnssi::symbols.
     */
    public function testGenerate()
    {
        $defaults = PasswordGeneratorAnssi::defaults();

        // Par défaut pour un mot de passe très fort ?
        $password = PasswordGeneratorAnssi::generate();
        $this->assertRegexp('/^[a-zA-Z0-9]{25}$/', $password);

        // Security.salt (CakePHP 2.x)
        $params = [
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false
        ];
        $password = PasswordGeneratorAnssi::generate(40, $params + $defaults);
        $this->assertRegExp('/^[a-zA-Z0-9]{40}$/', $password);

        // Security.cipherSeed (CakePHP 2.x)
        $params = [
            'lowercase_hexadecimal' => false,
            'uppercase_hexadecimal' => false,
            'lowercase_letters' => false,
            'uppercase_letters' => false,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false
        ];
        $password = PasswordGeneratorAnssi::generate(29, $params + $defaults);
        $this->assertRegExp('/^[0-9]{29}$/', $password);
    }
}
