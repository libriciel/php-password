# php-password

Bibliothèque PHP permettant de calculer la force et l'entropie d'un mot de passe,
de générer des mots de passes aléatoires.

## Contexte

On veut éviter que quelqu'un de non autorisé puisse s'authentifier dans notre
application.

- soit en testant des mots de passes de façon brute
- soit en ayant accès aux mots de passes stockés en base de données

### Besoins

- ralentir les essais en ligne
    - sur le serveur
        - [éviter les attaques DDOS](https://fr.wikipedia.org/wiki/Fail2ban)
            - https://github.com/SuperITMan/docker-fail2ban
            - http://www.the-lazy-dev.com/fr/installez-fail2ban-avec-docker/
            - https://doc.ubuntu-fr.org/fail2ban
            - https://www.youtube.com/watch?v=ge768xOLQJs
            - https://www.youtube.com/watch?v=-rmK50PbqCY
    - via l'interface graphique
        - ne pas indiquer si c'est le nom d'utilisateur ou le mot de passe qui est invalide
        - en cas d'échec, faire attendre de plus en plus longtemps (voir la [suite de Fibonacci](https://fr.wikipedia.org/wiki/Suite_de_Fibonacci))
- ralentir la découverte des mots de passe dans la base de données
    - stocker uniquement l'empreinte du mot de passe dans la base de données
    - utiliser un sel de sécurité, une longue chaîne de caractères accolée au mot de passe avant d'en calculer l'empreinte
    - utiliser un algorithme de calcul d'empreinte lent
- imposer des restrictions de mots de passe à l'utilisateur
    - force (difficulté, entropie) du mot de passe: le nombre de combinaisons possibles
        - dépend de la longueur
        - dépend du nombre de caractères possibles (nombres, alphabet, majuscules, accents, caractères spéciaux)
    - rejeter certains mots de passes
        - ceux rendus publics ou répandus
        - contenant trop de répétitions

### Utilisation de la librairie


#### Exemples

##### Force du mot de passe (selon l'ANSSI)

```php
use Libriciel\Password\Service\PasswordStrengthMeterAnssi;

$pwdStrengthAnssi = new  PasswordStrengthMeterAnssi();
// L'entropie, ici 61
$pwdStrengthAnssi->entropy('mot de passe');

// La force (entre 1, très faible et 5, très fort), ici 1
$pwdStrengthAnssi->strength('mot de passe');
```

> **Attention : l'entropie n'est exacte que pour un mot de passe aléatoire.**
>
> Le calcul (longueur × log2(nombre de symboles possibles)) suppose que chaque
> caractère est tiré au hasard. Pour un mot de passe **choisi par un utilisateur**,
> la force réelle est souvent bien plus faible : `Passw0rd!` obtient environ 55 bits
> alors qu'il figure dans tous les dictionnaires d'attaque. Le guide de l'ANSSI le
> précise :
>
> > « cette estimation ne vaut que si chaque caractère […] est choisi de manière
> > uniformément aléatoire. Dans le cas contraire […] l'entropie de tels mots de
> > passe se retrouve fortement réduite et son estimation réelle devient très
> > difficile. »
> > — [Guide ANSSI-PG-078](https://messervices.cyber.gouv.fr/documents-guides/anssi-guide-authentification_multifacteur_et_mots_de_passe.pdf), v2.0 du 08/10/2021, §4.1
>
> En pratique :
>
> - pour un mot de passe **généré** (par exemple avec `PasswordGeneratorAnssi`),
>   `strength()` mesure bien sa robustesse ;
> - pour un mot de passe **choisi**, `strength()` est une condition nécessaire mais
>   pas suffisante. Le guide recommande de la compléter par des règles de
>   complexité (R23) et par un contrôle de robustesse à la création (R27) :
>   comparaison à une liste de mots de passe courants ou compromis, détection des
>   motifs (`12345`, `azerty`), des informations personnelles (nom, date de
>   naissance) et de la réutilisation des anciens mots de passe.
>
> Les seuils de la bibliothèque (64, 80, 100 et 128 bits) viennent d'une ancienne
> page de l'ANSSI. Le guide de 2021 donne, à titre d'exemple, ≈ 65 bits (sensibilité
> faible à moyenne), ≈ 85 bits (moyenne à forte) et ≥ 100 bits (forte à très forte).

>
> Pour un mot de passe choisi, utiliser `PasswordValidatorAnssi` (ci-dessous).

##### Validation d'un mot de passe choisi (R21, R23 et R27)

`PasswordValidatorAnssi` contrôle, à la création ou au renouvellement d'un mot
de passe choisi par un utilisateur :

| Code (`VIOLATION_*`) | Recommandation | Contrôle |
|---|---|---|
| `length` | R22 | plus de 1 024 caractères, contre le déni de service ; les autres contrôles ne sont alors pas effectués |
| `entropy` | R21 | entropie (voir `PasswordStrengthMeterAnssi`) inférieure à celle demandée |
| `complexity` | R23 | moins de 3 classes parmi minuscules, majuscules, chiffres et autres caractères, sauf pour une phrase de passe de 20 caractères ou plus |
| `pattern` | R27 | suite (`abcd`, `4321`), ligne de clavier AZERTY ou QWERTY (`azer`, `qwer`), caractère répété 4 fois (`!!!!`) ou bloc répété (`abcabc`) |
| `dictionary` | R27 | mot de passe courant ou compromis, sans tenir compte de la casse, du leet speak (`P@ssw0rd`) ni des chiffres et symboles ajoutés au début ou à la fin (`Marseille2024!`) |
| `personal_data` | R27 | nom, prénom, identifiant, parties de l'adresse e-mail ou de la date de naissance, sans tenir compte de la casse, des accents ni du leet speak |

```php
use Libriciel\Password\Service\PasswordValidatorAnssi;

$validator = new PasswordValidatorAnssi();

// false : 85,8 bits, mais « marseille » figure dans le dictionnaire
$validator->validPassword('Marseille2024!', 80);

// true
$validator->validPassword('le chat dort sur le canapé vert', 80);

// Informations personnelles de l'utilisateur (facultatives)
$personalData = ['Jérôme', 'Dupont', 'jerome.dupont@exemple.fr', '12/03/1985'];

// ['personal_data'] : la liste des règles non respectées, à traduire pour l'utilisateur
$validator->violations('Dup0nt#Kw9!vLp2qZ', 80, $personalData);
```

Chaque règle peut aussi être contrôlée seule (`true` si elle est respectée),
indépendamment de l'entropie :

```php
$validator->validComplexity('kdjtmzpqrw');  // false : une seule classe (R23)
$validator->validPattern('Azerty!95kq');    // false : ligne de clavier « azer » (R27)
$validator->validDictionary('Soleil2024!'); // false : « soleil » est dans le dictionnaire (R27)
$validator->validLength($password);         // false au-delà de maxLength (R22)
```

Ces contrôles unitaires n'appliquent pas la longueur maximale : pour une saisie
utilisateur, appeler `validLength()` avant eux.

`validPassword()` regroupe tous les contrôles ; `violations()` renvoie les codes
de ceux qui échouent, toujours dans l'ordre du tableau ci-dessus.

Les règles se configurent dans le constructeur :

```php
use Libriciel\Password\Dictionary\FileDictionary;

$validator = new PasswordValidatorAnssi(
    dictionary: new FileDictionary(['/chemin/vers/ma-liste.txt']),
    minClasses: 3,        // classes de caractères exigées (R23), de 0 à 4
    passphraseLength: 20, // longueur à partir de laquelle les classes ne sont plus exigées
    patternLength: 4,     // longueur minimale d'une suite ou d'une répétition
    maxLength: 1024,      // longueur maximale (R22)
);
```

Le dictionnaire par défaut réunit environ 78 000 entrées (voir
[resources/dictionaries/NOTICE.md](resources/dictionaries/NOTICE.md)) :

- mots de passe courants français ([richelieu](https://github.com/tarraschk/richelieu), CC BY 4.0) ;
- mots de passe, prénoms et noms de famille courants internationaux
  ([zxcvbn](https://github.com/dropbox/zxcvbn), MIT) ;
- prénoms donnés en France depuis 1900 ([INSEE](https://www.insee.fr/fr/statistiques/8595130),
  Licence Ouverte 2.0), avec et sans accents.

Il n'est lu qu'à la première vérification (environ 15 ms et 8 Mo de mémoire). Une application peut fournir sa propre liste avec
`FileDictionary`, ou implémenter `PasswordDictionaryInterface` (par exemple pour
interroger [Have I Been Pwned](https://haveibeenpwned.com/API/v3#PwnedPasswords)).

La réutilisation des anciens mots de passe (R27) reste à contrôler par
l'application, qui seule connaît leur historique.

##### Génération de mots de passes aléatoires

Le cas d'usage est la création d'un mot de passe aléatoire à stocker hashé et
complété du salt, à utiliser sans devoir l'écrire via le lien (ayant une autre
valeur générée aléatoirement dans l'URL) d'un mail de remise à zéro de mot de passe.

Les caractères utilisés par défaut dans les mots de passes générés sont les 62
chiffres et lettres les plus courants de [Latin-1.](https://fr.wikipedia.org/wiki/ISO/CEI_8859-1):
de 0 à 9, a à z et de A à Z.

Par défaut est généré un mot de passe de longueur 40, correspondant à une force
de 5 pour une entropie de 238.

On n'est *jamais* certain d'avoir un caractère de chaque classe de caractère
configurée!

Dans le pire des cas, si `0000000000000000000000000000000000000000` est généré,
on a une force de 1 pour une entropie de 40.

```php
use Libriciel\Password\Service\PasswordGeneratorAnssi;

// Configuration complète par défaut
$config = [
    'binary'  => false,
    // de 0 à 9
    'numbers'  => true,
    'lowercase_hexadecimal' => false,
    'uppercase_hexadecimal' => false,
    // de a à z
    'lowercase_letters' => true,
    // A à Z
    'uppercase_letters' => true,
    'symbols_1' => false,
    'symbols_2' => false,
    // ()+-=_`{}~
    'symbols_3' => false,
    'lowercase_accents_fr' => false,
    'uppercase_accents_fr' => false
];

$pwdGeneratorAnssi = new PasswordGeneratorAnssi();
// Exemple de sortie: MUAVGHF4O5VPDboTwSJebjwNcryqYmRSS2izJHyJ
$pwdGeneratorAnssi->generate();
```

Si aucune classe de caractères n'est activée, `generate()` lève une
`Libriciel\Password\PasswordException`. C'est aussi le cas, pour toutes les
méthodes du générateur, si une classe de caractères n'existe pas (faute de frappe).

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
    - Restrictions, valeurs, ...
        - http://php.net/manual/en/function.hash.php#104987
        - http://php.net/manual/en/function.hash.php#94104
        - http://php.net/manual/en/function.hash.php#89574
        - https://fr.wikipedia.org/wiki/Whirlpool_(algorithme)
        - https://en.wikipedia.org/wiki/Whirlpool_(cryptography)

### Liens

- https://www.cnil.fr/fr/authentification-par-mot-de-passe-les-mesures-de-securite-elementaires
- https://www.cnil.fr/fr/generer-un-mot-de-passe-solide
- https://en.wikipedia.org/wiki/Password_strength#Entropy_as_a_measure_of_password_strength
- [Guide ANSSI-PG-078 : recommandations relatives à l'authentification multifacteur et aux mots de passe](https://messervices.cyber.gouv.fr/documents-guides/anssi-guide-authentification_multifacteur_et_mots_de_passe.pdf) (v2.0, 08/10/2021)
- https://www.ssi.gouv.fr/guide/mot-de-passe/
- https://www.ssi.gouv.fr/uploads/IMG/pdf/NP_MDP_NoteTech.pdf
- https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
- http://rumkin.com/tools/password/passchk.php
- https://www.e-services.uha.fr/mdp/
- https://madiba.encs.concordia.ca/~x_decarn/papers/password-meters-ndss2014.pdf

#### Développement

Construire un environnement docker tout prêt (`compose.dev.yaml`) :
```bash
make build
make bash
```

Contrôles lancés par la CI (`make help` pour la liste complète) :
```bash
make test      # PHPUnit
make phpstan   # analyse statique (niveau max)
make rector    # Rector en dry-run
make ecs       # standard de code (règles @Symfony, via ECS)
make audit     # composer audit
make fix-cs    # corrige le style (ECS puis Rector)
```




