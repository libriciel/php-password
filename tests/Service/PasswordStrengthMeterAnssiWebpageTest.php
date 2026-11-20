<?php

namespace LibricielTest\Password\Service;

use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\Service\PasswordStrengthMeterAnssi
 * avec les exemples donnés sur le site de l'ANSSI.
 *
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/}
 */
class PasswordStrengthMeterAnssiWebpageTest extends TestCase
{
    /**
     * Effectue des assertions à partir des $data sous forme longueur => valeur
     * attendue au moyen d'un bout de la chaîne de symboles coupée à la longueur
     * désirée.
     *
     * @param array<int, float|int> $data    Les valeur des assertions, en fonction de la longueur
     * @param string                $symbols Une chaîne d'au moins 30 caractères
     */
    protected function assertEqualEntropiesByLength(array $data, string $symbols): void
    {
        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        foreach ($data as $length => $expected) {
            $password = substr($symbols, 0, $length);
            $actual = $pwdStrengthMeter->entropy($password);
            $message = sprintf('longueur %d, %d symboles: %s', $length, $symbols, $password);
            $this->assertEquals($expected, $actual, $message);
        }
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 2
     * symboles (0 ou 1) et différentes longueurs.
     */
    public function testEntropy2Symbols(): void
    {
        $symbols = '000000000000000000000000000000';
        $data = [
            4 => 4,
            6 => 6,
            8 => 8,
            10 => 10,
            12 => 12,
            16 => 16,
            20 => 20,
            25 => 25,
            30 => 30,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 10
     * symboles (de 0 à 9) et différentes longueurs.
     */
    public function testEntropy10Symbols(): void
    {
        $symbols = '333333333333333333333333333333';
        $data = [
            4 => 13,
            6 => 20,
            8 => 27,
            10 => 33,
            12 => 40,
            16 => 53,
            20 => 66,
            25 => 83,
            30 => 100,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 16
     * symboles (de 0 à 9, a à f ou A à F) et différentes longueurs.
     */
    public function testEntropy16Symbols(): void
    {
        $symbols = '4F4F4F4F4F4F4F4F4F4F4F4F4F4F4F';
        $data = [
            4 => 16,
            6 => 24,
            8 => 32,
            10 => 40,
            12 => 48,
            16 => 64,
            20 => 80,
            25 => 100,
            30 => 120,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);

        $symbols = '4f4f4f4f4f4f4f4f4f4f4f4f4f4f4f';
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 26
     * symboles (de A à Z) et différentes longueurs.
     */
    public function testEntropy26Symbols(): void
    {
        $symbols = 'GGGGGGGGGGGGGGGGGGGGGGGGGGGGGG';
        $data = [
            4 => 19,
            6 => 28,
            8 => 38,
            10 => 47,
            12 => 56,
            16 => 75,
            20 => 94,
            25 => 118,
            30 => 141,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);

        $symbols = 'gggggggggggggggggggggggggggggg';
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 36
     * symboles (0 à 9 et A à Z) et différentes longueurs.
     */
    public function testEntropy36Symbols(): void
    {
        $symbols = '5G5G5G5G5G5G5G5G5G5G5G5G5G5G5G';
        $data = [
            4 => 21,
            6 => 31,
            8 => 41,
            10 => 52,
            12 => 62,
            16 => 83,
            20 => 103,
            25 => 129,
            30 => 155,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 52
     * symboles (A à Z, a à z) et différentes longueurs.
     */
    public function testEntropy52Symbols(): void
    {
        $symbols = 'gZgZgZgZgZgZgZgZgZgZgZgZgZgZgZ';
        $data = [
            4 => 23,
            6 => 34,
            8 => 46,
            10 => 57,
            12 => 68,
            16 => 91,
            20 => 114,
            25 => 143,
            30 => 171,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 62
     * symboles (0 à 9, A à Z, a à z) et différentes longueurs.
     */
    public function testEntropy62Symbols(): void
    {
        $symbols = '5Ht5Ht5Ht5Ht5Ht5Ht5Ht5Ht5Ht5Ht';
        $data = [
            4 => 24,
            6 => 36,
            8 => 48,
            10 => 60,
            12 => 71,
            16 => 95,
            20 => 119,
            25 => 149,
            30 => 179,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 70
     * symboles (0 à 9, A à Z, a à z et  ! #$*% ?) et différentes longueurs.
     */
    public function testEntropy70Symbols(): void
    {
        $symbols = 'Hz3%Hz3%Hz3%Hz3%Hz3%Hz3%Hz3%Hz';
        $data = [
            4 => 25,
            6 => 37,
            8 => 49,
            10 => 61,
            12 => 74,
            16 => 98,
            20 => 123,
            25 => 153,
            30 => 184,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }

    /**
     * Tests de la méthode PasswordStrengthMeterAnssi::entropy() avec 70
     * symboles (0 à 9, A à Z, a à z et  ! #$*% ? &[|]@^µ§ :/ ;.,<>°²³) et
     * différentes longueurs.
     */
    public function testEntropy90Symbols(): void
    {
        $symbols = '9kL!@9kL!@9kL!@9kL!@9kL!@9kL!@';
        $data = [
            // 4 est impossible car il faut 5 caractères minimum
            6 => 39,
            8 => 52,
            10 => 65,
            12 => 78,
            16 => 104,
            20 => 130,
            25 => 162,
            30 => 195,
        ];
        $this->assertEqualEntropiesByLength($data, $symbols);
    }
}
