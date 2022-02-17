<?php

namespace Libriciel\Password\Service;

use Exception;
use Libriciel\Password\PasswordException;

use function mb_str_split;

/**
 * Cette classe permet de générer des mots de passes aléatoires suivant les
 * recommandations de l'ANSSI.
 *
 * @see {@url https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/}
 */
class PasswordGeneratorAnssi
{
    /**
     * Les chaînes de caractères autorisées, la clé est le nom de leur classe.
     *
     * @var array
     */
    protected static array $classes = [
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
    protected static array $defaults = [
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
    protected array $config = [];

    /**
     * Retourne la configuration en cours après l'avoir au préalable complétée
     * par les valeurs par défaut et la configuration passée en paramètres.
     *
     * @param array $config La configuration à modifier.
     * @return array
     */
    public function config(array $config = []): array
    {
        $this->config = $config + $this->config + static::$defaults;
        return $this->config;
    }

    /**
     * Retourne le paramétrage par défaut des classes à utiliser lors de la
     * génération.
     *
     * @return array
     */
    public function defaults(): array
    {
        return self::$defaults;
    }

    /**
     * Retourne la liste des caractères en fonction des classes à utiliser.
     *
     * @param array $params Les paramètres définissant les classes à utiliser
     * @param ?string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractères interne (mb_internal_encoding)
     *  sera utilisé.
     * @return array
     */
    public function characters(array $params = [], string $encoding = null): array
    {
        $myConfig = $params + $this->config();
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $accumulator = [];

        foreach ($myConfig as $key => $enabled) {
            if (true === $enabled) {
                $tokens = mb_str_split(static::$classes[$key], 1, $encoding);
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
     * @param ?string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractères interne (mb_internal_encoding)
     *  sera utilisé.
     * @return string
     * @throws Exception
     */
    public function generate(int $length = 40, array $params = [], string $encoding = null): string
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $characters = $this->characters($params, $encoding);
        $count = count($characters);
        $result = false;

        if ($count > 0) {
            $result = '';
            $max = 0;
            while ($max < $length) {
                $random = random_int(0, $count - 1);
                $result .= $characters[$random];
                $max++;
            }
        }

        return $result;
    }

    /**
     * Génère un mot de passe aléatoire de la longueur désirée avec certaines
     * classes de caractères devant être utilisées automatiquement.
     *
     * @param integer $length La longueur du mot de passe à générer.
     * @param array $params Les paramètres définissant les classes à utiliser.
     * @param array $mandatory Les paramètres définissant les classes à utiliser obligatoirement.
     * @param ?string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractères interne (mb_internal_encoding)
     *  sera utilisé.
     * @return string
     * @throws Exception
     */
    public function constrained(
        int $length = 40,
        array $params = [],
        array $mandatory = [],
        string $encoding = null
    ): string {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $characters = static::characters($params, $encoding);
        $count = count($characters);

        if ($count === 0) {
            $format = 'Impossible de générer un mot de passe de longueur %d sans caractère disponible';
            $message = sprintf($format, $length, array_sum($mandatory), json_encode($mandatory));
            throw new PasswordException($message);
        }

        if (array_sum($mandatory) > $length) {
            $format =
                'Impossible de générer un mot de passe de longueur %d ' .
                'avec %d caractères obligatoires (obligatoires: %s)';
            $message = sprintf($format, $length, array_sum($mandatory), json_encode($mandatory));
            throw new PasswordException($message);
        }

        $result = [];
        $max = 0;

        foreach ($mandatory as $class => $number) {
            if (! isset(static::$classes[$class])) {
                throw new PasswordException("La classe de caractère $class n'est pas défini");
            }
            $stringLength = mb_strlen(static::$classes[$class], $encoding);
            for ($idx = 0; $idx < $number; $idx++) {
                $char = mb_substr(static::$classes[$class], random_int(0, $stringLength - 1), 1, $encoding);
                $result[] = $char;
                $max++;
            }
        }

        while ($max < $length) {
            $random = random_int(0, $count - 1);
            $result[] = $characters[$random];
            $max++;
        }

        return implode('', $this->randomArray($result));
    }

    /**
     * @param array $array
     * @return array
     * @throws Exception
     */
    private function randomArray(array $array): array
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
