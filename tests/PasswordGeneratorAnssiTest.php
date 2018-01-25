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
     * Test de la méthode PasswordGeneratorAnssi::generate() au moyen de la méthode
     * PasswordStrengthMeterAnssi::symbols.
     */
    public function testGenerate()
    {
        $params = [
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false,
            'symbols_1' => false,
            'symbols_2' => false,
        ];
        $password = PasswordGeneratorAnssi::generate(128, $params);
        $actual = PasswordStrengthMeterAnssi::symbols($password);
        $this->assertEquals(62, $actual);
    }
    
    // Security.salt -> DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi
    // Security.cipherSeed -> 76859309657453542496749683645
}
