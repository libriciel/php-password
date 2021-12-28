<?php

use Libriciel\Utility\Password\PasswordGeneratorAnssi;
use Libriciel\Utility\Password\PasswordStrengthMeterAnssi;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'basics.php';
require_once __DIR__ . DS . 'PasswordGeneratorAnssi.php';
require_once __DIR__ . DS . 'PasswordStrengthMeterInterface.php';
require_once __DIR__ . DS . 'PasswordStrengthMeterAnssi.php';

$defaults = [
    'debug' => false,
    'length' => 40,
    'max' => 1
];


$shortopts = ['h', 'd', 'l::', 'm::'];
$longopts = ['debug', 'help', 'length::', 'max::'];

$passwordGeneratorAnsi = new PasswordGeneratorAnssi();

$config = $passwordGeneratorAnsi->config();
$config_keys = array_keys($config);
foreach ($config_keys as $config_key) {
    $longopts[] = "{$config_key}::";
}

$options = [];
foreach (getopt(implode('', $shortopts), $longopts) as $key => $value) {
    switch ($key) {
        case 'h':
        case 'help':
            usage($defaults, $passwordGeneratorAnsi->config());
            exit(0);
        case 'd':
        case 'debug':
            $options['debug'] = true;
            break;
        case 'l':
        case 'length':
            $options['length'] = $value;
            break;
        case 'm':
        case 'max':
            $options['max'] = $value;
            break;
        case in_array($key, $config_keys):
            $config[$key] = bool_opt($value);
            break;
        default:
            break;
    }
}

$options += $defaults;
$passwordGeneratorAnsi->config($config);

if ($options['debug'] === true) {
    printf("Generating %s password(s) of length %d\n", $options['max'], $options['length']);
    foreach ($config_keys as $config_key) {
        printf("  %s: %s\n", $config_key, $config[$config_key] ? 'true' : 'false');
    }
    printf("\n");
}
$passwordStrengthMeter = new PasswordStrengthMeterAnssi();

for ($i = 1; $i <= $options['max']; $i++) {
    $password = $passwordGeneratorAnsi->generate($options['length']);
    if ($options['debug'] === true) {
        printf("%s\n", $password);
        printf("  - length: %s\n", mb_strlen($password));
        printf("  - entropy: %s\n", $passwordStrengthMeter->entropy($password));
        printf("  - strength: %s\n", $passwordStrengthMeter->strength($password));
    } else {
        printf("%s\n", $password);
    }
}
