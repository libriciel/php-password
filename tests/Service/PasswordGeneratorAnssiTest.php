<?php

namespace LibricielTest\Password\Service;

use Libriciel\Password\PasswordException;
use Libriciel\Password\Service\PasswordGeneratorAnssi;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Utility\Password\PasswordGeneratorAnssi.
 */
class PasswordGeneratorAnssiTest extends TestCase
{
    /**
     * Configuration par défaut.
     * @var array<string, bool>
     */
    protected array $defaultConfig = [
        'binary' => false,
        'numbers' => true,
        'lowercase_hexadecimal' => false,
        'uppercase_hexadecimal' => false,
        'lowercase_letters' => true,
        'uppercase_letters' => true,
        'symbols_1' => false,
        'symbols_2' => false,
        'lowercase_accents_fr' => false,
        'uppercase_accents_fr' => false,
    ];

    /**
     * Test de la méthode PasswordGeneratorAnssi::defaults().
     */
    public function testDefaults(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $actual = $pwdGeneratorAnsi->defaults();
        $this->assertEquals($this->defaultConfig, $actual);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate() avec la configuration
     * par défaut.
     */
    public function testGenerateDefaultConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // Par défaut pour un mot de passe très fort ?
        $password = $pwdGeneratorAnsi->generate();
        self::assertNotFalse($password);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $password);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::generate().
     */
    public function testGenerate(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();

        // Security.salt (CakePHP 2.x)
        $password = $pwdGeneratorAnsi->generate(40, $defaults);
        self::assertNotFalse($password);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{40}$/', $password);

        // Security.cipherSeed (CakePHP 2.x)
        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false,
        ];
        $password = $pwdGeneratorAnsi->generate(29, $params + $defaults);
        self::assertNotFalse($password);
        $this->assertMatchesRegularExpression('/^[0-9]{29}$/', $password);
    }

    /**
     * Test de la configuration via PasswordGeneratorAnssi::config().
     */
    public function testConfig(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        // On récupère la configuration par défaut
        $actual = $pwdGeneratorAnsi->config();
        $this->assertEquals($this->defaultConfig, $actual);

        $params = [
            'lowercase_letters' => false,
            'uppercase_letters' => false,
        ];
        $actual = $pwdGeneratorAnsi->config($params);
        $expected = [
            'binary' => false,
            'numbers' => true,
            'lowercase_hexadecimal' => false,
            'uppercase_hexadecimal' => false,
            'lowercase_letters' => false,
            'uppercase_letters' => false,
            'symbols_1' => false,
            'symbols_2' => false,
            'lowercase_accents_fr' => false,
            'uppercase_accents_fr' => false,
        ];
        $this->assertEquals($expected, $actual);

        $actual = $pwdGeneratorAnsi->generate(20);
        self::assertNotFalse($actual);
        $this->assertMatchesRegularExpression('/^[0-9]{20}$/', $actual);

        // Retour aux valeurs par défaut
        $pwdGeneratorAnsi->config($this->defaultConfig);
    }

    /**
     * Test de la méthode PasswordGeneratorAnssi::constrained().
     */
    public function testConstrained(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
            'uppercase_letters' => 1,
            'symbols_1' => 1,
        ];

        // Entropie 37, force 1
        $password = $pwdGeneratorAnsi->constrained(6, $defaults, $mandatory);

        $this->assertNotFalse(mb_ereg('^([a-zA-Z0-9]|[€\\!#\\$\\*%\\? ]){6}$', $password));
        $this->assertNotFalse(mb_ereg('[a-z]', $password));
        $this->assertNotFalse(mb_ereg('[A-Z]', $password));
        $this->assertNotFalse(mb_ereg('[0-9]', $password));
        $this->assertNotFalse(mb_ereg('[€\\!#\\$\\*%\\? ]', $password));
    }

    public function testConstrainedException1(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Impossible de générer un mot de passe de longueur 1 avec 2 caractères obligatoires'
        );
        $pwdGeneratorAnsi->constrained(1, $defaults, $mandatory);
    }

    public function testConstrainedException2(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $defaults = $pwdGeneratorAnsi->defaults();
        $false = array_combine(array_keys($defaults), array_fill(0, count($defaults), false));
        $mandatory = [
            'numbers' => 1,
            'lowercase_letters' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Impossible de générer un mot de passe de longueur 3 sans caractère disponible'
        );
        $pwdGeneratorAnsi->constrained(3, $false, $mandatory);
    }

    public function testWithUnexpectedClass(): void
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $mandatory = [
            'numbers' => 1,
            'not_existing' => 1,
        ];
        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            "La classe de caractère not_existing n'est pas défini"
        );
        $pwdGeneratorAnsi->constrained(3, [], $mandatory);
    }

    /**
     * Test que la méthode constrained lance une exception quand une classe de caractères est vide
     */
    public function testConstrainedWithEmptyCharacterClass(): void
    {
        // On crée une classe anonyme qui hérite de PasswordGeneratorAnssi
        // pour pouvoir tester avec une classe de caractères vide
        $pwdGeneratorAnsi = new class extends PasswordGeneratorAnssi {
            protected static array $classes = [
                'numbers' => '0123456789',
                'empty_class' => '', // Classe vide pour le test
            ];
        };

        $mandatory = [
            'numbers' => 1,
            'empty_class' => 1, // On demande un caractère de la classe vide
        ];

        $this->expectException(PasswordException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            "La classe de caractère empty_class est vide"
        );

        $pwdGeneratorAnsi->constrained(10, [], $mandatory);
    }
}
