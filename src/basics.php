<?php

/**
 * Définitions et fonctions basiques.
 *
 * @license CeCiLL V2
 */

if (false === defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

if (false === defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (false === function_exists('debug')) {
    /**
     * Affiche le contenu d'une variable en console ou au format HTML.
     *
     * @see CakePHP 2.x / 3.x
     *
     * @param mixed $var
     * @param bool $showHtml La sortie doit-elle être au format HTML ?
     */
    function debug($var, $showHtml = null)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = str_replace(array(ROOT), '', $trace[0]['file']);
        $line = $trace[0]['line'];

        $var = var_export($var, true);

        if ((PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') && $showHtml !== true) {
            $template = <<<TEXT
%s
########## DEBUG ##########
%s
###########################

TEXT;
            $lineInfo = sprintf('%s (line %s)', $file, $line);
        } else {
            $template = <<<HTML
<div class="cake-debug-output">
%s
<pre class="cake-debug">
%s
</pre>
</div>
HTML;
            $var = htmlspecialchars($var, ENT_QUOTES);
            $lineInfo = sprintf('<span><strong>%s</strong> (line <strong>%s</strong>)</span>', $file, $line);
        }
        printf($template, $lineInfo, $var);
    }
}

if (false === function_exists('mb_str_split')) {
    /**
     * Convertit une chaîne de caractères en tableau.
     *
     * FALSE est retourné si length est inférieur à 1.
     * Si length est supérieur à la longueur de string, la chaîne
     * entière est retournée dans le premier (et seul) élément du tableau.
     *
     * @see {@url http://www.php.net/manual/en/function.str-split.php}
     *
     * @param string $string La chaîne d'entrée
     * @param int $length Longueur maximale de chaque élément
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne (mb_internal_encoding)
     *  sera utilisé.
     * @return false|array
     */
    function mb_str_split($string, $length = 1, $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $result = false;

        if ($length >= 1) {
            $result = [];
            $strlen = mb_strlen($string, $encoding);
            for ($idx = 0; $idx < $strlen; $idx += $length) {
                $result[] = mb_substr($string, $idx, $length, $encoding);
            }
        }

        return $result;
    }
}

if (false === function_exists('random_array')) {
    /**
     * Retourne les éléments d'un array dont les clés ont été mélangées.
     *
     * @param array $array Un array ayant des clés numériques
     * @return array
     */
    function random_array(array $array)
    {
        $result = [];
        $keys = array_keys($array);

        while (count($keys) > 0) {
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
