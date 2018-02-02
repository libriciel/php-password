<?php
/**
 * Code source de la classe Libriciel\Utility\Password\PasswordGeneratorAnssi
 *
 * @license CeCiLL V2
 */
namespace Libriciel\Utility\Password;

/**
 * Cette classe permet de générer des mots de passes aléatoires suivant les
 * recommandations de l'ANSSI.
 *
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/}
 */
abstract class PasswordGeneratorAnssi
{
    /**
     * Les chaînes de caractères autorisées, le clé est le nom de leur classe.
     *
     * @var array
     */
    protected static $classes = [
        'binary'  => '01',
        'numbers'  => '0123456789',
        'lowercase_hexadecimal' => '0123456789abcdef',
        'uppercase_hexadecimal' => '0123456789ABCDEF',
        'lowercase_letters' => 'abcdefghijklmnopqrstuvwxyz',
        'uppercase_letters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'symbols_1' => '€!#$*% ?',
        'symbols_2' => '&[|]@^µ§\:/;.,<>°²³\'"',
        'lowercase_accents_fr' => 'àâäæçèéêëîïôœùûü',
        'uppercase_accents_fr' => 'ÀÂÄÆÇÈÉÊËÎÏÔŒÙÛÜ'
    ];

    /**
     * Les valeurs par défaut des classes à paramétrer pour la génération.
     *
     * @var array
     */
    protected static $defaults = [
        'binary'  => false,
        'numbers'  => true,
        'lowercase_hexadecimal' => false,
        'uppercase_hexadecimal' => false,
        'lowercase_letters' => true,
        'uppercase_letters' => true,
        'symbols_1' => false,
        'symbols_2' => false,
        'lowercase_accents_fr' => false,
        'uppercase_accents_fr' => false
    ];

    /**
     * Configuration.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * Retourne la configuration en cours après l'avoir au préalable complétée
     * par les valeurs par défaut et la configuration passée en paramètres.
     *
     * @param array $config La configuration à modifier.
     * @return array
     */
    public static function config(array $config = [])
    {
        static::$config = $config + static::$config + static::$defaults;
        return static::$config;
    }

    /**
     * Retourne le paramétrage par défaut des classes à utiliser lors de la
     * génération.
     *
     * @return array
     */
    public static function defaults()
    {
        return self::$defaults;
    }

    /**
     * Retourne la liste des caractères en fonction des classes à utiliser.
     *
     * @param array $params Les paramètres définissant les classes à utiliser
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne (mb_internal_encoding)
     *  sera utilisé.
     * @return array
     */
    public static function characters(array $params = [], $encoding = null)
    {
        $classes = $params + static::config();
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $accumulator = [];

        foreach ($classes as $key => $enabled) {
            if (true === $enabled) {
                $tokens = \mb_str_split(static::$classes[$key], 1, $encoding);
                $accumulator = array_merge($accumulator, $tokens);
            }
        }

        return array_values(array_unique($accumulator));
    }

    /**
     * Génère un mot de passe aléatoire de la longueur désirée.
     *
     * @param integer $length La longueur du mot de passe à générer.
     * @param array $params Les paramètres définissant les classes à utiliser.
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne (mb_internal_encoding)
     *  sera utilisé.
     * @return type
     */
    public static function generate($length = 40, array $params = [], $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $characters = static::characters($params, $encoding);
        $count = count($characters);
        $result = false;

        if ($count > 0) {
            $result = '';
            $max = 0;
            while ($max<$length) {
                $random = random_int(0, $count-1);
                $result .= $characters[$random];
                $max++;
            }

            /*//------------------------------------------------------------------
            // Permet de forcer des caractères de certaines classes
            //------------------------------------------------------------------
            //@fixme
            $test = str_pad('', $length, ' ');
            $mandatory = [
                'numbers' => 1,
                'lowercase_letters' => 1,
                'uppercase_letters' => 1,
                'symbols_1' => 1,
                'symbols_2' => 1,
                'lowercase_accents_fr' => 1,
                'uppercase_accents_fr' => 1,
            ];
            //\debug(array_sum($mandatory));//@todo si > $length -> exception
            foreach ($mandatory as $class => $number) {//@fixme: si class n'existe pas -> exception ?
                $strlen = mb_strlen(static::$classes[$class], $encoding);
                for ($idx = 0 ; $idx < $number ; $idx++) {
                    $char = mb_substr(static::$classes[$class], random_int(0, $strlen-1), 1, $encoding);
                    $test .= $char;
                    $max++;
                }
            }
            //@fixme: longueur + mélanger
            $test = mb_substr($test, array_sum($mandatory)-1);
            \debug($test);*/
        }

        return $result;
    }
}
