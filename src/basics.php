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
        $lineInfo = sprintf('%s (line %s)', $file, $line);
        $template = null;

        if ((PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') || $showHtml === false) {
            $template = <<<TEXT
%s
########## DEBUG ##########
%s
###########################

TEXT;
        } elseif ($showHtml === null) {
            $showHtml = true;
        }

        $var = var_export($var, true);

        if ($showHtml === true) {
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
