<?php
/**
 * Code source de la classe Libriciel\Utility\Password\PasswordHasher
 *
 * @license CeCiLL V2
 */
namespace Libriciel\Utility\Password;

/**
 * La classe PasswordHasher permet de générer simplement une empreinte des données
 * pour permettre leur stockage.
 * Un sel de sécurité et un algorithme peuvent être configurés par défaut.
 *
 * @see {@url http://php.net/manual/fr/function.password-hash.php}
 * @see {@url http://php.net/manual/fr/function.crypt.php}
 * @see {@url http://php.net/manual/fr/faq.passwords.php#faq.passwords.fasthash}
 * @see {@url http://php.net/manual/fr/password.constants.php}
 * @see {@url http://php.net/manual/fr/book.hash.php}
 * @see {@url https://github.com/cakephp/cakephp/blob/2.x/lib/Cake/Utility/Security.php}
 */
abstract class PasswordHasher
{
    /**
     * Configuration par défaut.
     *
     * @var array
     */
    protected static $defaults = [
        'algorithm' => 'sha256',
        'salt' => null,
        'raw_output' => false
    ];

    /**
     * Configuration.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * Vérification de la configuration passée en paramètre.
     *
     * @param array $config La configuration que l'on va utiliser.
     * @throws \RuntimeException Si "algorithm" n'est pas disponible.
     */
    protected static function configCheck(array $config)
    {
        $algorithms = hash_algos();
        
        if (false === in_array($config['algorithm'], $algorithms, true)) {
            $format = "L'algorithme %s n'est pas disponible; merci de choisir une valeur parmi %s";
            sort($algorithms);
            $message = sprintf($format, $config['algorithm'], implode(', ', $algorithms));
            throw new \RuntimeException($message);
        }
    }

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
        static::configCheck(static::$config);
        return static::$config;
    }

    /**
     * Retourne le hash d'un mot de passe, xxx.
     *
     * @param string $password
     * @param array $config
     * @return string
     */
    public static function hash($password, array $config = [])
    {
        $config += static::config();
        static::configCheck($config);
        
        if (true === empty((string)$config['salt'])) {
            $message = "Il faudrait utiliser un sel de hashage (aléatoire, d'au moins 40 caractères)";
            trigger_error(sprintf($message, get_called_class()), E_USER_NOTICE);
        }

        return hash(
            $config['algorithm'],
            (string)$password.(string)$config['salt'],
            $config['raw_output']
        );
    }
}
