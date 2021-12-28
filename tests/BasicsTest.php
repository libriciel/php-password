<?php

namespace LibricielTest\Utility\Password;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires des fonctions basiques.
 */
class BasicsTest extends TestCase
{

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
