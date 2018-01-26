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
     * @param string $encoding Le paramètre encoding est l'encodage des caractères.
     *  S'il est omis, l'encodage de caractres interne (mb_internal_encoding)
     *  sera utilisé.
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
