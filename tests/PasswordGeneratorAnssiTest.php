<?php

/**
 * Code source de la classe LibricielTest\Utility\Password\PasswordGeneratorAnssiTest
 *
 * @license CeCiLL V2
 */

namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\TestCase;
use Libriciel\Utility\Password\PasswordGeneratorAnssi;

/**
 * Tests unitaires de la classe Libriciel\Utility\Password\PasswordGeneratorAnssi
 */
class PasswordGeneratorAnssiTest extends TestCase
{
    /**
     * Configuration par défaut.
     *
     * @var array
     */
    protected $defaultConfig = [
        'binary'  => false,
        'numbers'  => true,
        'lowercase_hexadecimal' => false,
        'uppercase_hexadecimal' => false,
        'lowercase_letters' => true,
        'uppercase_letters' => true,
        'symbols_1' => false,
        'symbols_2' => false,
        'lowercase_accents_fr' => false,
        'uppercase_accents_fr' => false
    ];

    /**
     * Test de la méthode PasswordGeneratorAnssi::defaults().
     */
    public function testDefaults()
    {
        $actual = PasswordGeneratorAnssi::defaults();
        $this->assertEquals($this->defaultConfig, $actual);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate() avec la configuration
     * par défaut.
     */
    public function testGenerateDefaultConfig()
    {
        $defaults = PasswordGeneratorAnssi::defaults();

        // Par défaut pour un mot de passe très fort ?
        $password = PasswordGeneratorAnssi::generate();
        $this->assertRegexp('/^[a-zA-Z0-9]{40}$/', $password);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate().
     */
    public function testGenerate()
    {
        $defaults = PasswordGeneratorAnssi::defaults();

        // Security.salt (CakePHP 2.x)
        $password = PasswordGeneratorAnssi::generate(40, $defaults);
        $this->assertRegExp('/^[a-zA-Z0-9]{40}$/', $password);

        // Security.cipherSeed (CakePHP 2.x)
        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false
        ];
        $password = PasswordGeneratorAnssi::generate(29, $params + $defaults);
        $this->assertRegExp('/^[0-9]{29}$/', $password);
    }


    /**
     * Test de la configuration via PasswordGeneratorAnssi::config().
     */
    public function testConfig()
    {
        // On récupère la configuration par défaut
        $actual = PasswordGeneratorAnssi::config();
        $this->assertEquals($this->defaultConfig, $actual);

        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false
        ];
        $actual = PasswordGeneratorAnssi::config($params);
        $expected = [
            'binary'  => false,
            'numbers'  => true,
            'lowercase_hexadecimal' => false,
            'uppercase_hexadecimal' => false,
            'lowercase_letters' => false,
            'uppercase_letters' => false,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false
        ];
        $this->assertEquals($expected, $actual);

        $actual = PasswordGeneratorAnssi::generate(20);
        $this->assertRegexp('/^[0-9]{20}$/', $actual);

        // Retour aux valeurs par défaut
        PasswordGeneratorAnssi::config($this->defaultConfig);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained().
     */
    public function testConstrained()
    {
        $defaults = PasswordGeneratorAnssi::defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
            'uppercase_letters' => 1,
            'symbols_1' => 1
        ];

        // Entropie 37, force 1
        $password = PasswordGeneratorAnssi::constrained(6, $defaults, $mandatory);

        $this->assertNotFalse(mb_ereg('^([a-zA-Z0-9]|[€\\!#\\$\\*%\\? ]){6}$', $password));
        $this->assertNotFalse(mb_ereg('[a-z]', $password));
        $this->assertNotFalse(mb_ereg('[A-Z]', $password));
        $this->assertNotFalse(mb_ereg('[0-9]', $password));
        $this->assertNotFalse(mb_ereg('[€\\!#\\$\\*%\\? ]', $password));
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained().
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage Impossible de générer un mot de passe de longueur 1 avec 2 caractères obligatoires
     * (obligatoires: {"numbers":1,"lowercase_letters":1})
     */
    public function testConstrainedException1()
    {
        $defaults = PasswordGeneratorAnssi::defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1
        ];
        PasswordGeneratorAnssi::constrained(1, $defaults, $mandatory);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained().
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage Impossible de générer un mot de passe de longueur 3 sans caractère disponible
     */
    public function testConstrainedException2()
    {
        $defaults = PasswordGeneratorAnssi::defaults();
        $false = array_combine(array_keys($defaults), array_fill(0, count($defaults), false));
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1
        ];
        PasswordGeneratorAnssi::constrained(3, $false, $mandatory);
    }
}
