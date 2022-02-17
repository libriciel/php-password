<?php

namespace Libriciel\Password\Command;

use Libriciel\Password\Service\PasswordGeneratorAnssi;
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PasswordGeneratorCommand extends Command
{
    private const DEFAULT_PASSWORD_LENGTH = 40;
    private const DEFAULT_NB_PASSWORD_GENERATED = 1;

    protected function configure()
    {
        $this
            ->setName("app:password:generate")
            ->setDescription("Generate one (or many) random passwords\n")
            ->addOption(
                "length",
                "l",
                InputOption::VALUE_REQUIRED,
                "Length of the passwords",
                self::DEFAULT_PASSWORD_LENGTH
            )
            ->addOption(
                "max",
                "m",
                InputOption::VALUE_REQUIRED,
                "Number of generated passwords",
                self::DEFAULT_NB_PASSWORD_GENERATED
            );


        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();

        $config = $pwdGeneratorAnsi->config();
        foreach ($config as $configKey => $defaultValue) {
            $this->addOption($configKey, null, InputOption::VALUE_REQUIRED, "", $defaultValue);
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pwdGeneratorAnsi = new PasswordGeneratorAnssi();
        $config = $pwdGeneratorAnsi->config();
        $options = [];
        foreach (array_keys($config) as $configKeys) {
            $options[$configKeys] =  (bool) filter_var($input->getOption($configKeys), FILTER_VALIDATE_BOOL);
        }

        if ($output->isVerbose()) {
            $output->writeln(sprintf(
                "Generation of %d passwords of length %d\n",
                $input->getOption('max'),
                $input->getOption('length')
            ));
            foreach ($options as $configKeys => $values) {
                $output->writeln("$configKeys : $values");
            }
            $output->writeln("");
        }

        $pwdStrengthMeter = new PasswordStrengthMeterAnssi();

        for ($i = 0; $i < $input->getOption('max'); $i++) {
            $password = $pwdGeneratorAnsi->generate(
                $input->getOption('length'),
                $options
            );
            $output->write($password);
            $output->write(
                sprintf(
                    " - entropy : %f - strength : %d",
                    $pwdStrengthMeter->entropy($password),
                    $pwdStrengthMeter->strength($password)
                ),
                false,
                OutputInterface::VERBOSITY_VERBOSE
            );
            $output->writeln("");
        }
        return Command::SUCCESS;
    }
}
