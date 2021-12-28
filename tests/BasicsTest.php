<?php

/**
 * Code source des tests unitaires des fonctions basiques PHP.
 *
 * @license CeCiLL V2
 */

namespace LibricielTest\Utility;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires des fonctions basiques.
 */
class BasicsTest extends TestCase
{
    /**
     * Test de la fonction mb_str_split.
     */
    public function testMbStrSplit()
    {
        $string = 'Hello Friend';
        $expected = ['H', 'e', 'l', 'l', 'o', ' ', 'F', 'r', 'i', 'e', 'n', 'd'];
        $this->assertEquals($expected, \mb_str_split($string));
    }

    /**
     * Test de la fonction mb_str_split en spécifiant la longueur.
     */
    public function testMbStrSplitSplitLength()
    {
        $string = 'Hello Friend';
        $expected = ['Hello', ' Frie', 'nd'];
        $this->assertEquals($expected, \mb_str_split($string, 5));
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
        $this->assertEquals($expected, \mb_str_split($string, 5, 'ISO-8859-1'));
    }

    /**
     * Test des cas aux limites de la fonction mb_str_split.
     */
    public function testMbStrSplitEdgeCases()
    {
        $string = 'Un été comme un autre.';

        // Erreur : chaîne de longueur 0
        // Not working with the basics php function
        //$this->assertFalse(mb_str_split($string, 0));

        $this->assertEquals([$string], \mb_str_split($string, 66));
    }

    /**
     * Test de la fonction random_array.
     */
    public function testRandomArray()
    {
        // Avec un array vide
        $this->assertEquals([], random_array([]));

        // Avec quelques éléments
        $array = [1, 2, 3];
        $actual = random_array($array);
        // @fixme: il se pourrait que l'on retrouve l'array de départ
        // heuh ben du coup, le test fonctionne pas tout le temps...
        //$this->assertNotEquals($array, $actual);
        sort($actual);
        $this->assertEquals($array, $actual);
    }
}
