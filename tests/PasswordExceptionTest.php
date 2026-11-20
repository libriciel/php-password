<?php

declare(strict_types=1);

namespace LibricielTest\Password;

use Libriciel\Password\PasswordException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de la classe Libriciel\Password\PasswordException.
 */
class PasswordExceptionTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $previous = new \LogicException('précédente');
        $exception = new PasswordException('message', 42, $previous);

        // Contrat d'API vérifié à l'exécution, garanti statiquement
        $this->assertInstanceOf(\RuntimeException::class, $exception); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame('message', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
