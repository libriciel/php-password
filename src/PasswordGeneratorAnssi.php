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
        'binary'  => true,
        'numbers'  => true,
        'lowercase_hexadecimal' => true,
        'uppercase_hexadecimal' => true,
        'lowercase_letters' => true,
        'uppercase_letters' => true,
        'symbols_1' => false,
        'symbols_2' => false,
        'lowercase_accents_fr' => false,
        'uppercase_accents_fr' => false
    ];

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
        $defaults = static::defaults();
        $classes = $params + $defaults;
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $accumulator = [];

        foreach ($classes as $key => $enabled) {
            if (true === $enabled) {
                $tokens = mb_str_split(static::$classes[$key], 1, $encoding);
                $accumulator = array_merge($accumulator, $tokens);
            }
        }

        return array_values(array_unique($accumulator));
    }

    /**
     * Génère un mot de passe aléatoire d'une certaine longueur.
     *
     * Par défaut un mot de passe très fort (d'entropie 149), facile à taper, de
     * 25 caractères parmi 62 (0 à 9, a à z, A à Z).
     *
     * @param integer $length La longueur du mot de passe à générer.
     * @param array $params Les paramètres définissant les classes à utiliser.
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne (mb_internal_encoding)
     *  sera utilisé.
     * @return type
     */
    public static function generate($length = 25, array $params = [], $encoding = null)
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
        }

        return $result;
    }
}
