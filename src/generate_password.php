<?php
namespace Libriciel\Utility\Password;

// @todo use ne fonctionne pas, pourquoi?!!
require_once __DIR__.DIRECTORY_SEPARATOR.'basics.php';
require_once __DIR__.DS.'PasswordGeneratorAnssi.php';
require_once __DIR__.DS.'PasswordStrengthMeterInterface.php';
require_once __DIR__.DS.'PasswordStrengthMeterAnssi.php';

$defaults = [
    'debug' => false,
    'length' => 40,
    'max' => 1
];

/**
 * Affiche l'aide du programme.
 * 
 * @param array $defaults
 * @param array $config
 */
function usage(array $defaults, array $config)
{
    $basename = basename(__FILE__);
    printf("NAME\n");
    printf("  %s\n", $basename);
    printf("\nDESCRIPTION\n");
    printf("  Génère une ou plusieurs chaîne(s) de caractères aléatoires.\n");
    printf("  Il est possible de spécifier différentes options, dont les classes de caractères à utiliser.\n");
    printf("  Ces classes sont celles utilisées par la classe PasswordGeneratorAnssi.\n");
    printf("\nSYNOPSIS\n");
    printf("  %s [OPTION]...\n", $basename);
    printf("\nOPTIONS\n");
    printf("  -h --help\t\t\tAffiche cette aide\n");
    printf("  -l --length\t\t\tSpécifie la longueur des mots de passe générés. Par défaut: %d\n", $defaults['length']);
    printf("  -m --max\t\t\tSpécifie le nombre de mots de passe à générer. Par défaut: %d\n", $defaults['max']);
    foreach ($config as $key => $default) {
        $tabs = 3 - (int)floor(strlen($key)/8);
        $spacer = str_repeat("\t", $tabs);
        printf("     --{$key}{$spacer}Configuration de cette classe de caractères. Par défaut: %s\n", $default ? 'true' : 'false');
    }
    printf("\nEXEMPLES\n");
    printf("  %s -h\n", $basename);
    printf("  %s -l=100 -m=10 --symbols_1=true\n", $basename);
    printf("  %s -l=29 -m=5 --lowercase_letters=false --uppercase_letters=false\n", $basename);
}

function bool_opt($value)
{
    $value = strtolower($value);

    if (is_string($value) && in_array($value, ['true', 'false'])) {
        $result = $value === 'true' ? true : false;
    } else {
        $result = (bool)$value;
    }

    return $result;
}

$shortopts = ['h', 'd', 'l::', 'm::'];
$longopts = ['debug', 'help', 'length::', 'max::'];

$config = PasswordGeneratorAnssi::config();
$config_keys = array_keys($config);
foreach ($config_keys as $config_key) {
    $longopts[] = "{$config_key}::";
}

$options = [];
foreach(getopt(implode('', $shortopts), $longopts) as $key => $value) {
    switch($key) {
        case 'h':
        case 'help':
            usage($defaults, PasswordGeneratorAnssi::config());
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
    }
}

$options += $defaults;
PasswordGeneratorAnssi::config($config);

if ($options['debug'] === true) {
    printf("Generating %s password(s) of length %d\n", $options['max'], $options['length']);
    foreach ($config_keys as $config_key) {
        printf("  %s: %s\n", $config_key, $config[$config_key] ? 'true' : 'false');
    }
    printf("\n");
}

for ($i=1;$i<=$options['max'];$i++) {
    $password = PasswordGeneratorAnssi::generate($options['length']);
    if ($options['debug'] === true) {
        printf("%s\n", $password);
        printf("  - length: %s\n", mb_strlen($password));
        printf("  - entropy: %s\n", PasswordStrengthMeterAnssi::entropy($password));
        printf("  - strength: %s\n", PasswordStrengthMeterAnssi::strength($password));
    } else {
        printf("%s\n", $password);
    }
}
