<?php
/**
 * Code source des tests unitaires de l'extension multibyte pour PHP.
 *
 * @license CeCiLL V2
 */
namespace LibricielTest\Utility;

use PHPUnit\Framework\TestCase;

define('DS', DIRECTORY_SEPARATOR);
require_once dirname(__FILE__).DS.'..'.DS.'src'.DS.'php-multibyte-extension.php';

/**
 * Tests unitaires de la la librairie php-multibyte-extension.
 */
class UtilityMultibyteTest extends TestCase
{
    /**
     * Test de la fonction mb_str_split.
     */
    public function testMbStrSplit()
    {
        $string = 'Hello Friend';
        $expected = ['H', 'e', 'l', 'l', 'o', ' ', 'F', 'r', 'i', 'e', 'n', 'd'];
        $this->assertEquals($expected, mb_str_split($string));
    }

    /**
     * Test de la fonction mb_str_split en spécifiant la longueur.
     */
    public function testMbStrSplitSplitLength()
    {
        $string = 'Hello Friend';
        $expected = ['Hello', ' Frie', 'nd'];
        $this->assertEquals($expected, mb_str_split($string, 5));
    }

    /**
     * Test de la fonction mb_str_split en spécifiant l'encodage.
     */
    public function testMbStrSplitEncoding()
    {
        $string = utf8_decode('Un été comme un autre.');
        $expected = ['Un ét', 'é com', 'me un', ' autr', 'e.'];
        foreach ($expected as $idx => $token) {
            $expected[$idx] = utf8_decode($token);
        }
        $this->assertEquals($expected, mb_str_split($string, 5, 'ISO-8859-1'));
    }

    /**
     * Test des cas aux limites de la fonction mb_str_split.
     */
    public function testMbStrSplitEdgeCases()
    {
        $string = 'Un été comme un autre.';

        // Erreur: chaîne de longueur 0
        $this->assertFalse(mb_str_split($string, 0));

        $this->assertEquals([$string], mb_str_split($string, 66));
    }
}
