<?php
/**
 * Code source des tests unitaires des fonctions basiques PHP.
 *
 * @license CeCiLL V2
 */
namespace LibricielTest\Utility;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires des fonctions basiques.
 */
class BasicsTest extends TestCase
{
    /**
     * Test de la fonction debug en mode CLI.
     */
    public function testDebugCli()
    {
        $file = preg_replace('/'.preg_quote(ROOT, '/').'/', '', __FILE__);
        ob_start();
        debug('Hello Friend');
        $line = __LINE__ - 1;
        $actual = ob_get_contents();
        ob_end_clean();
        $expected = $file." (line ".$line.")
########## DEBUG ##########
'Hello Friend'
###########################\n";
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test de la fonction debug en mode HTML.
     */
    public function testDebugHtml()
    {
        $file = preg_replace('/'.preg_quote(ROOT, '/').'/', '', __FILE__);
        ob_start();
        debug('Hello Friend', true);
        $line = __LINE__ - 1;
        $actual = ob_get_contents();
        ob_end_clean();
        $expected = "<div class=\"cake-debug-output\">
<span><strong>".$file."</strong> (line <strong>".$line."</strong>)</span>
<pre class=\"cake-debug\">
&#039;Hello Friend&#039;
</pre>
</div>";
        $this->assertEquals($expected, $actual);
    }
}
