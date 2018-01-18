# php-password

Librairie PHP permettant de calculer la force et l'entropie d'un mot de passe et de générer des mots de passes aléatoires.

## Documentation

- xkcd
    - https://www.explainxkcd.com/wiki/index.php/936:_Password_Strength
    - https://www.explainxkcd.com/wiki/index.php/792:_Password_Reuse
- https://www.kaspersky.com/blog/password-check/
- zxcvbn
    - https://www.theguardian.com/technology/2016/aug/19/password-strength-meters-security
    - https://blogs.dropbox.com/tech/2012/04/zxcvbn-realistic-password-strength-estimation/
    - https://fr.wiktionary.org/wiki/Wiktionnaire:Listes_de_fr%C3%A9quence#Fran%C3%A7ais
    - https://github.com/bjeavons/zxcvbn-php
    - https://www.usenix.org/conference/usenixsecurity16/technical-sessions/presentation/wheeler

### Liens

- https://en.wikipedia.org/wiki/Password_strength#Entropy_as_a_measure_of_password_strength
- https://www.ssi.gouv.fr/guide/mot-de-passe/
- https://www.ssi.gouv.fr/uploads/IMG/pdf/NP_MDP_NoteTech.pdf
- https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
- http://rumkin.com/tools/password/passchk.php
- https://www.e-services.uha.fr/mdp/
- https://madiba.encs.concordia.ca/~x_decarn/papers/password-meters-ndss2014.pdf

### Code PHP

- https://adullact.net/scm/viewvc.php/webrsa/trunk/app/Plugin/Password/

#### Portage

##### Qualité du code

```bash
sudo apt-get install php-xdebug
```

```bash
rm -rf out/* \
&& vendor/bin/phpunit \
    --log-junit=out/phpunit.xml \
    --whitelist=src \
    --coverage-html=out/coverage \
    --coverage-clover=out/phpunit-coverage.xml \
    tests \
&& vendor/bin/phpmd \
    src \
    xml \
    cleancode,codesize,controversial,design,naming,unusedcode \
    --reportfile out/phpmd.xml \
; vendor/bin/phpcs \
    --report=checkstyle \
    --standard=PSR2 \
    src \
    tests \
    > out/phpcs.xml \
; vendor/bin/phploc \
    --count-tests \
    --log-xml=out/phploc.xml \
    src \
    tests \
&& vendor/bin/phpcpd \
    --log-pmd=out/phpcpd.xml \
    src \
    tests \
&& vendor/bin/phpdoc \
    -t out/phpdoc \
    -d src \
    -d tests \
&& vendor/bin/phpcb \
    -o out/phpcb \
    -s src \
    -s tests \
    --log out \
&& mkdir -p out/pdepend \
&& vendor/bin/pdepend \
    --coverage-report=out/phpunit-coverage.xml \
    --dependency-xml=out/pdepend/dependency.xml \
    --jdepend-chart=out/pdepend/jdepend-chart.svg \
    --jdepend-xml=out/pdepend/jdepend.xml \
    --overview-pyramid=out/pdepend/overview-pyramid.svg \
    --summary-xml=out/pdepend/summary.xml \
    src
```

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