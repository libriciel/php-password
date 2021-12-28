<?php

if (false === function_exists('random_array')) {
    /**
     * Retourne les éléments d'un array dont les clés ont été mélangées.
     *
     * @param array $array Un array ayant des clés numériques
     * @return array
     */
    function random_array(array $array): array
    {
        $result = [];
        $keys = array_keys($array);

        while (! empty($keys)) {
            $idx = random_int(0, count($keys) - 1);
            $key = $keys[$idx];
            $result[] = $array[$key];
            array_splice($keys, $idx, 1);
        }

        return $result;
    }
}


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
        $tabs = 3 - (int)floor(strlen($key) / 8);
        $spacer = str_repeat("\t", $tabs);
        printf(
            "     --{$key}{$spacer}Configuration de cette classe de caractères. Par défaut: %s\n",
            $default ? 'true' : 'false'
        );
    }
    printf("\nEXEMPLES\n");
    printf("  %s -h\n", $basename);
    printf("  %s -l=100 -m=10 --symbols_1=true\n", $basename);
    printf("  %s -l=29 -m=5 --lowercase_letters=false --uppercase_letters=false\n", $basename);
}

function bool_opt($value): bool
{
    $value = strtolower($value);

    if (in_array($value, ['true', 'false'])) {
        return ($value === 'true');
    }

    return (bool)$value;
}
