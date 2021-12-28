<?php

namespace LibricielTest\Password\Command;

use Libriciel\Password\Command\PasswordGeneratorCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class PasswordGeneratorCommandTest extends TestCase
{
    public function testCommand()
    {
        $command = new PasswordGeneratorCommand();
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
        $this->assertEquals(41, mb_strlen($commandTester->getDisplay()));
    }

    public function testCommandWithVerbose()
    {
        $command = new PasswordGeneratorCommand();
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]));
    }
}
