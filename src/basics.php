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
     * @see CakePHP 2.9.8
     *
     * @param mixed $var
     */
    function debug($var)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = str_replace(array(ROOT), '', $trace[0]['file']);
        $line = $trace[0]['line'];
        $lineInfo = sprintf('%s (line %s)', $file, $line);

        $html = <<<HTML
<div class="cake-debug-output">
%s
<pre class="cake-debug">
%s
</pre>
</div>
HTML;
        $text = <<<TEXT
%s
########## DEBUG ##########
%s
###########################

TEXT;

        if (PHP_SAPI === 'cli') {
            $template = $text;
        }
        $var = var_export($var, true);
        if (PHP_SAPI !== 'cli') {
            $template = $html;
            $var = htmlspecialchars($var, ENT_QUOTES);
            $lineInfo = sprintf('<span><strong>%s</strong> (line <strong>%s</strong>)</span>', $file, $line);
        }
        printf($template, $lineInfo, $var);
    }
}
