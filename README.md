# php-password

Librairie PHP permettant de calculer la force et l'entropie d'un mot de passe et de générer des mots de passes aléatoires.

## Documentation

### Liens

- https://en.wikipedia.org/wiki/Password_strength#Entropy_as_a_measure_of_password_strength
- https://www.ssi.gouv.fr/guide/mot-de-passe/
- https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
- http://rumkin.com/tools/password/passchk.php
- https://www.e-services.uha.fr/mdp/
- https://xkcd.com/936/

### Code PHP

- https://adullact.net/scm/viewvc.php/webrsa/trunk/app/Plugin/Password/

#### as@lae

```php
$checkPassword = function ($value) {
    $chars = 0;
    if (preg_match('/[a-z]/', $value)) {
        $chars += 26;
    }
    if (preg_match('/[A-Z]/', $value)) {
        $chars += 26;
    }
    if (preg_match('/[0-9]/', $value)) {
        $chars += 10;
    }
    if (preg_match('/[ ! #$*%]/', $value)) {
        $chars += 8;
    }
    if (preg_match('/[^a-zA-Z0-9 ! #$*%]/', $value)) {
        $chars += 20;
    }
    $score = strlen($value) * log($chars, 2);
    return $score >= Configure::read('Password.complexity');
};
```

```php
/**
 * Force des mot de passe
 * @see https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
 */
/**
 * Mot de passe de 8 caractères dans un alphabet de 70 symboles
 * Taille usuelle
 */
define('PASSWORD_ULTRA_WEAK', 49);
/**
 * Mot de passe de 12 caractères dans un alphabet de 90 symboles
 * Taille minimale recommandée par l’ANSSI pour des mots de passe ergonomiques ou utilisés de façon locale.
 */
define('PASSWORD_WEAK', 78);
/**
 * Mot de passe de 16 caractères dans un alphabet de 36 symboles
 * Taille recommandée par l’ANSSI pour des mots de passe plus sûrs.
 */
define('PASSWORD_MEDIUM', 82);
/**
 * Mot de passe de 20 caractères dans un alphabet de 90 symboles
 * Force équivalente à la plus petite taille de clé de l’algorithme de chiffrement standard AES (128 bits).
 */
define('PASSWORD_STRONG', 130);
```