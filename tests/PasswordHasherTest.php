<?php

/**
 * Code source de la classe LibricielTest\Utility\Password\PasswordHasherTest
 *
 * @license CeCiLL V2
 */

namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use Libriciel\Utility\Password\PasswordHasher;

/**
 * Tests unitaires de la classe Libriciel\Utility\Password\PasswordHasher.
 */
class PasswordHasherTest extends TestCase
{
    /**
     * Avec un salt vide (par défaut), en supprimant la notice.
     *
     * @see {@url http://reverse-hash-lookup.online-domain-tools.com/}
     */
    public function testHashEmptySalt()
    {
        $password = 'admin';
        $actual = @PasswordHasher::hash($password);
        $expected = '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918';
        $this->assertEquals($expected, $actual);

        $password = 'p0Db2Ajnydy76m5BXbLiMiIem';
        $actual = @PasswordHasher::hash($password);
        $expected = '36fe250f0e36faf404b66ba8b50436e6c7546fe5e104b90f4b6b685f360931fe';
        $this->assertEquals($expected, $actual);
    }

    /**
     * Avec un salt vide par défaut, vérification de la notice.
     */
    public function testHashEmptySaltException()
    {
        $exception = "Il faudrait utiliser un sel de hashage (aléatoire, d'au"
            . " moins 40 caractères)";
        $this->expectException(Notice::class);
        $this->expectExceptionMessage($exception);
        PasswordHasher::hash('p0Db2Ajnydy76m5BXbLiMiIem');
    }

    /**
     * Avec un algorithme non disponible.
     */
    public function testHashAlgorithmException()
    {
        $regexp = '/^L\'algorithme f00 n\'est pas disponible; merci de choisir une valeur parmi .+$/';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp($regexp);
        PasswordHasher::hash('p0Db2Ajnydy76m5BXbLiMiIem', ['algorithm' => 'f00']);
    }

    /**
     * Test de la méthode PasswordHasher::hash avec un salt de 40 caractères.
     */
    public function testHash()
    {
        $salt = 'cgGoFEFiex1diAZJhNfnkx68tmxdyBKxsvN2wFbV';
        $password = 'p0Db2Ajnydy76m5BXbLiMiIem';
        $expected = '3ab871fac6613d871ddd65990bcd4c3a01478d02847b3216e972483377fa0861';

        // En passant le salt en paramètre de l'appel
        $actual = PasswordHasher::hash($password, ['salt' => $salt]);
        $this->assertEquals($expected, $actual);

        $oldConfig = PasswordHasher::config();
        $this->assertNull($oldConfig['salt']);

        // En déclarant le salt par défaut
        PasswordHasher::config(['salt' => $salt]);
        $actual = PasswordHasher::hash($password);
        PasswordHasher::config($oldConfig);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la méthode PasswordHasher::hash avec la valeur par défaut du salt
     * d'une installation CakePHP 2.x.
     */
    public function testHashCakephp2()
    {
        $salt = '968dda000668f21e8761de477a102017fe2ca4c5c6c947a5a42fb5fb813964be';
        $password = 'p0Db2Ajnydy76m5BXbLiMiIem';
        $expected = '647a70b5ce5318fd2010442c7e7ec4ae837777fb04be68513aabd7be0f83a7d6';

        $actual = PasswordHasher::hash($password, ['salt' => $salt]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la configuration recommandée avec les méthodes PasswordHasher::config
     * et PasswordHasher::hash.
     */
    public function testRecommendedConfig()
    {
        $config = [
            'algorithm' => 'sha256', // 64 caractères à la sortie
            // 64 caractères a-z et 0-9 (36 symboles) pour le salt (entropie de 256)
            'salt' => '968dda000668f21e8761de477a102017fe2ca4c5c6c947a5a42fb5fb813964be',
            'raw_output' => false
        ];
        PasswordHasher::config($config);

        $actual = PasswordHasher::hash('mot de passe');
        $expected = 'ab4d9bad79648740b6d9a569f66fb530bc104506d37a440941cef7187a519dc7';
        $this->assertEquals($expected, $actual);
    }
}
