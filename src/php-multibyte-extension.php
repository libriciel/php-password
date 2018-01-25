<?php
/**
 * Code source de l'extension des fonctions multibyte.
 *
 * @license CeCiLL V2
 */

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
     * @param string $encoding
     * @return false|array
     */
    function mb_str_split($string, $length = 1, $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $result = false;

        if ($length >=1) {
            $result = [];
            $strlen = mb_strlen($string, $encoding);
            for ($idx=0; $idx<$strlen; $idx+=$length) {
                $result[] = mb_substr($string, $idx, $length, $encoding);
            }
        }

        return $result;
    }
}

if (false === function_exists('randomize_string')) {
    /**
     * Mélange les caractères d'une chaîne de caractères multibyte grâce à des
     * fonctions cryptographiquement sécurisées.
     *
     * @see {@url http://php.net/manual/fr/function.str-shuffle.php}
     * @see {@url http://php.net/manual/fr/function.random-int.php}
     *
     * @param string $string La chaîne d'entrée
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne sera utilisé.
     * @return array
     */
    function randomize_string($string, $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        $length = mb_strlen($string, $encoding);
        $result = '';

        while($length > 0) {
            $position = random_int(0, $length-1);
            $result .= mb_substr($string, $position, 1, $encoding);
            $string = mb_substr($string, 0, $position, $encoding)
                . mb_substr($string, $position+1, $length-$position, $encoding);
            $length--;
        }
        
        return $result;
    }
}
