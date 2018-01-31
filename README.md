# php-password

Librairie PHP permettant de calculer la force et l'entropie d'un mot de passe et
de générer des mots de passes aléatoires.

## Exemples de mots de passes

### 2 symboles 0 ou 1 (code binaire)

- Très faible, 0 <= entropie < 64, moins de 64 caractères: `00111`
- Faible, 64 <= entropie < 80, de 64 à 79 caractères: `1001011011110111000001110111000001111000000100111000101110011110`
- Moyenne, 80 <= entropie < 100, de 80 à 99 caractères: `11110001011001110010111100111100011001100001101001100100111110011011111100001000`
- Forte, 100 <= entropie < 128, de 100 à 127 caractères: `0100010111111110101100011111100110110111011000111100110100100011110101111101001010000111100101111010`
- Très forte, 128 <= entropie, plus de 127 caractères: `00110010011111110111011000101111011111100110000010100110000011001001110110110011011011110100110111100010000101001010101010001110`

### 10 symboles de 0 à 9 (code décimal)

- Très faible, 0 <= entropie < 64, moins de 20 caractères: `54612`
- Faible, 64 <= entropie < 80, de 20 à 24 caractères: `96442571499252715213`
- Moyenne, 80 <= entropie < 100, de 25 à 30 caractères: `5478444456254874224762975`
- Forte, 100 <= entropie < 128, de 31 à 38 caractères: `7241455799436811802114240877052`
- Très forte, 128 <= entropie, plus de 38 caractères: `375112021368327243201601633713856751474`

### 16 symboles 0 à 9 et A à F (code hexadécimal)

- Très faible, 0 <= entropie < 64, moins de 16 caractères: `870E0`
- Faible, 64 <= entropie < 80, de 16 à 19 caractères: `6A183F3AE2B00906`
- Moyenne, 80 <= entropie < 100, de 20 à 24 caractères: `65C88BAED3D6ECC0B705`
- Forte, 100 <= entropie < 128, de 25 à 31 caractères: `61ABB6F019F76328F0D62C267`
- Très forte, 128 <= entropie, plus de 31 caractères: `D9DA43DA38F70012416EF268A86A7E8B`

### 26 symboles de A à Z (code alphabétique)

- Très faible, 0 <= entropie < 64, moins de 14 caractères: `JQMVC`
- Faible, 64 <= entropie < 80, de 14 à 17 caractères: `BLMQNYZLABYOIC`
- Moyenne, 80 <= entropie < 100, de 18 à 21 caractères: `PHUAZZNJLOIYFHPHCA`
- Forte, 100 <= entropie < 128, de 22 à 27 caractères: `KSFUNDDVTJWHHPKGXUNZJB`
- Très forte, 128 <= entropie, plus de 27 caractères: `YFJRELLOVUQQUOUAGWLIHXXQIWKU`

### 36 symboles 0 à 9 et A à Z (code alphanumérique)

- Très faible, 0 <= entropie < 64, moins de 13 caractères: `1UGQN`
- Faible, 64 <= entropie < 80, de 13 à 15 caractères: `Z7L14FUEP48KK`
- Moyenne, 80 <= entropie < 100, de 16 à 19 caractères: `OOF5WYZW1PDGRNQW`
- Forte, 100 <= entropie < 128, de 20 à 24 caractères: `Q6H3E9SUPB5N4Q830KCM`
- Très forte, 128 <= entropie, plus de 24 caractères: `8HSTJHN4X13EEF87A457QZ6FK`

### 52 symboles A à Z et a à z

- Très faible, 0 <= entropie < 64, moins de 12 caractères: `lrPju`
- Faible, 64 <= entropie < 80, de 12 à 14 caractères: `WSRuLKnVgzik`
- Moyenne, 80 <= entropie < 100, de 15 à 17 caractères: `IOxQLzDMvtkGGJq`
- Forte, 100 <= entropie < 128, de 18 à 22 caractères: `FKCaVbPwSMEjaETgqG`
- Très forte, 128 <= entropie, plus de 22 caractères: `edYwUOyseOFvMckpBAWcpqs`

### 62 symboles 0 à 9, A à Z et a à z

- Très faible, 0 <= entropie < 64, moins de 11 caractères: `YqSp4`
- Faible, 64 <= entropie < 80, de 11 à 13 caractères: `8Z8pwoUtXAJ`
- Moyenne, 80 <= entropie < 100, de 14 à 16 caractères: `V4d1zH3em5BVMn`
- Forte, 100 <= entropie < 128, de 17 à 21 caractères: `d3qe2nfe8S417Kd35`
- Très forte, 128 <= entropie, plus de 21 caractères: `4rVeh6NoPV3X0DzvhFs9tL`

### 70 symboles 0 à 9, A à Z, a à z et !#$*% ?

- Très faible, 0 <= entropie < 64, moins de 11 caractères: `8P$uw`
- Faible, 64 <= entropie < 80, de 11 à 13 caractères: `6jmw8fYf%M8`
- Moyenne, 80 <= entropie < 100, de 14 à 16 caractères: `8€rSvNuXCk1YTj`
- Forte, 100 <= entropie < 128, de 17 à 20 caractères: `%?a3M!UT?XMQBNqfx`
- Très forte, 128 <= entropie, plus de 20 caractères: `zLpQrNdC!qj!mRdjtPW€54612`

### 90 symboles 9, A à Z, a à z et !#$*% ?&[|]@^µ§ :/;.,<>°²³

- Très faible, 0 <= entropie < 64, moins de 10 caractères: `tJ?Ve?9&²g`
- Faible, 64 <= entropie < 80, de 10 à 12 caractères: `%46°QxY§#v`
- Moyenne, 80 <= entropie < 100, de 13 à 15 caractères: `a1t^Ywpj5 ;€o`
- Forte, 100 <= entropie < 128, de 16 à 19 caractères: `V&j8oTQ&Ah§AµK4#`
- Très forte, 128 <= entropie, plus de 19 caractères: `KW6 F§9b/x²O&*Fv€er[`

## Documentation

- Computerfile
    - https://www.youtube.com/watch?v=7U-RbOKanYs
    - https://www.youtube.com/watch?v=3NjQ9b3pgIg
- Vérifier la sécurité des mots de passe
    - https://hashcat.net/hashcat/
    - http://reverse-hash-lookup.online-domain-tools.com/
- Liste de mots de passes communs
    - http://www.passwordrandom.com/most-popular-passwords
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
- @todo
    - https://security.stackexchange.com/questions/118450/is-there-a-threshold-for-a-password-so-long-it-doesnt-get-any-more-secure-or-ev
    - https://nakedsecurity.sophos.com/2016/08/18/nists-new-password-rules-what-you-need-to-know/
    - https://auth0.com/blog/dont-pass-on-the-new-nist-password-guidelines/

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
sudo apt-get install \
    ant \
    php-xdebug
```

```bash
rm -rf out/* \
&& vendor/bin/phpunit \
    --log-junit=out/phpunit.xml \
    --whitelist=src \
    --coverage-html=out/coverage \
    --coverage-clover=out/phpunit-coverage.xml \
    tests \
; vendor/bin/phpmd \
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
; vendor/bin/phpcpd \
    --log-pmd=out/phpcpd.xml \
    src \
    tests \
; vendor/bin/phpdoc \
    -t out/phpdoc \
    -d src \
    -d tests \
; vendor/bin/phpcb \
    -o out/phpcb \
    -s src \
    -s tests \
    --log out \
; mkdir -p out/pdepend \
; vendor/bin/pdepend \
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