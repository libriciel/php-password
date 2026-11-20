# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/)
et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [4.0.0] - non publiée

### Ajouté

- Classe de caractères `symbols_3` (`()+-=_\`{}~`, 10 symboles), désactivée par
  défaut dans `PasswordGeneratorAnssi` et reconnue par
  `PasswordStrengthMeterAnssi::symbols()`. Ces caractères ASCII, dont `-` et `_`, ne
  faisaient partie d'aucune classe : ils ne comptaient que dans la longueur. Tout
  l'ASCII imprimable est désormais couvert, conformément à la recommandation R23 du
  guide ANSSI-PG-078 (« un jeu de caractères le plus large possible »). L'entropie et
  la force augmentent pour les mots de passe qui en contiennent (#13).
- `PasswordValidatorAnssi::validPassword()` et `violations()` : validation d'un
  mot de passe choisi par un utilisateur selon le guide ANSSI-PG-078, avec
  l'entropie minimale souhaitée (R21), des règles de complexité assouplies pour les
  phrases de passe (R23), la détection des suites, lignes de clavier et répétitions,
  d'un dictionnaire de mots de passe courants et des informations personnelles
  (R27). `violations()` renvoie les codes des règles non respectées ;
  `validComplexity()`, `validPattern()` et `validDictionary()` contrôlent chaque
  règle séparément (#15).
- Dictionnaire d'environ 78 000 mots de passe, prénoms et noms courants, dans
  `resources/dictionaries` : mots de passe français
  ([richelieu](https://github.com/tarraschk/richelieu), CC BY 4.0), mots de passe,
  prénoms et noms internationaux ([zxcvbn](https://github.com/dropbox/zxcvbn), MIT),
  prénoms donnés en France depuis 1900 ([INSEE](https://www.insee.fr/fr/statistiques/8595130),
  Licence Ouverte 2.0). Il est remplaçable via `PasswordDictionaryInterface` ou
  `FileDictionary` (#15).

### Modifié

- **Rupture de compatibilité** : `PasswordGeneratorAnssi::generate()` lève une
  `PasswordException` lorsque aucune classe de caractères n'est activée, au lieu de
  renvoyer `false` (ce qui provoquait une `TypeError`) (#1).
- **Rupture de compatibilité** : PHP 8.3 minimum, PHP 8.1 n'étant plus maintenu
  et PHP 8.2 ne l'étant plus après le 31 décembre 2026 ; la CI teste PHP 8.3 à 8.5 (#4).
- **Rupture de compatibilité** : une classe de caractères inconnue, même désactivée
  (faute de frappe comme `'symbol_1' => false`), lève une `PasswordException` dans
  `config()`, `characters()`, `generate()` et `constrained()` de
  `PasswordGeneratorAnssi`, au lieu d'un warning et d'une dépréciation. Le message
  liste les classes inconnues et les classes existantes ; il remplace « La classe de
  caractère X n'est pas défini » pour les classes obligatoires de `constrained()` (#7).
- Paramètres `$encoding` explicitement nullables (`?string`), ce qui supprime les
  dépréciations de PHP 8.4 sur les types implicitement nullables.
- Types des tableaux précisés en PHPDoc (`array<string, bool>`, `list<string>`…)
  dans `PasswordGeneratorAnssi` et `PasswordStrengthMeterAnssi`, pour les projets
  qui les analysent avec PHPStan ou Psalm ; la baseline PHPStan (47 erreurs) est
  supprimée (#9).
- Constantes `STRENGTH_*` de `PasswordStrengthMeterInterface` typées `int`
  (PHP 8.3) : une implémentation qui les redéfinit doit garder des entiers (#9).
- `declare(strict_types=1)` dans tous les fichiers, imposé par ECS (#11).
- Le namespace des tests `LibricielTest\Password` passe dans `autoload-dev` : il
  n'est plus chargé par les projets qui utilisent la bibliothèque (#11).
- Code au standard `@Symfony`, vérifié par ECS, qui remplace PHP CS Fixer (#2).
- Dépendances de développement à jour, dont PHPUnit 12.5, dernière version
  compatible avec PHP 8.3 ; data providers statiques déclarés par attributs (#3, #4).
- Image Docker de développement et de CI en PHP 8.5 ; PHP 8.6 n'est pas encore
  supporté par pcov ni par Xdebug (#3).
- Images Docker épinglées par digest (`tag@sha256:…`) dans le Dockerfile et la CI (#3).
- CI alignée sur le socle Libriciel : SAST, détection de secrets, analyse des
  dépendances, `composer audit`, scan de l'image (composant `vulnerability-scan`),
  `check-libriciel-standards`, Renovate, PHPStan (niveau max), Rector et tests sur
  chaque version de PHP supportée (#2).
- Environnement de développement dans `compose.dev.yaml`, cibles `make test`,
  `phpstan`, `rector`, `ecs`, `audit` et `qa` (#2).

### Corrigé

- `PasswordStrengthMeterAnssi::strength()` compare aux seuils l'entropie non arrondie :
  un mot de passe juste sous un seuil n'est plus classé au niveau supérieur (par
  exemple 17 majuscules, 79,9 bits, passent de « moyenne » à « faible »). La force
  renvoyée baisse d'un niveau pour ces mots de passe (#10).
- `PasswordStrengthMeterAnssi::symbols()` reconnaît l'antislash (`\`), que le
  générateur peut produire (classe `symbols_2`) : il était ignoré, ce qui
  sous-estimait l'entropie et la force des mots de passe qui en contiennent (#5).
- `PasswordStrengthMeterAnssi::entropy()` renvoie `0` pour une chaîne vide ou
  composée uniquement de caractères hors classes (`ñ`, `’`…), au lieu
  de `NAN` ou `-INF`, qui faisaient par exemple échouer `json_encode()`. La force
  reste « très faible ». Les caractères hors classes mêlés à d'autres comptent
  toujours dans la longueur, mais pas dans le nombre de symboles (#6).

### Supprimé

- `bin/console`, qui ne fonctionnait plus depuis la suppression de la commande de
  génération et de `symfony/console` en 3.0.0 (#11).
- PHPMD et PHP_CodeSniffer, jamais lancés (#2).
- Dépendance de développement `sebastian/phpcpd`, abandonnée depuis 2020 (#3).

### Sécurité

- Mise à jour des dépendances de développement.
- Le jeton GitHub est passé au build Docker en secret BuildKit plutôt qu'en
  `--build-arg`, qui l'aurait exposé dans l'historique de l'image (#2).

### Documentation

- README : avertissement sur la portée de l'entropie, exacte pour les mots de passe
  aléatoires mais seulement condition nécessaire pour ceux choisis par
  l'utilisateur, avec les recommandations R23 et R27 et l'écart entre les seuils de
  la bibliothèque et ceux du guide de 2021. Le guide ANSSI-PG-078 remplace, comme
  référence des docblocks, l'ancienne page de l'ANSSI qui n'est plus en ligne (#14).
- README : namespace `Libriciel\Password\Service` dans les exemples et commandes de
  développement via `make` / `docker compose` (#1).

## [3.0.2] - 2026-09-17

Tag posé sur le même commit que la 3.0.1 : aucune modification de code.

## [3.0.1] - 2023-02-23

### Corrigé

- `PasswordStrengthMeterAnssi::entropy()` renvoyait une chaîne au lieu d'un `float`.
- Correction du `composer.json` et mise à jour des dépendances.

## [3.0.0] - 2022-02-22

### Modifié

- PHP 8.1 minimum.

### Supprimé

- Commande Symfony de génération de mot de passe (inutilisable depuis le programme
  qui importe la bibliothèque).

## [2.0.1] - 2021-12-29

### Supprimé

- Dépendance `paragonie/random_compat` (PHP 5 n'est plus supporté).

## [2.0.0] - 2021-12-29

### Ajouté

- Fork du projet `cbuffin/php-password`.

### Modifié

- La commande de génération de mot de passe utilise la console Symfony.
- Classes déplacées dans le namespace `Libriciel\Password\Service`.

### Supprimé

- Calcul de l'empreinte du mot de passe : utiliser la fonction native `password_hash()`.

## [1.1.0] - 2018-10-26

### Ajouté

- Script `generate_password.php` pour générer des mots de passe aléatoires en ligne
  de commande avec `PasswordGeneratorAnssi`.
- Liens de référence dans le README.

## [1.0.0] - 2018-03-19

Version initiale.

[4.0.0]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/3.0.2...master
[3.0.2]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/3.0.1...3.0.2
[3.0.1]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/3.0.0...3.0.1
[3.0.0]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/2.0.1...3.0.0
[2.0.1]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/2.0.0...2.0.1
[2.0.0]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/1.1.0...2.0.0
[1.1.0]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/compare/1.0.0...1.1.0
[1.0.0]: https://gitlab.libriciel.fr/libriciel/direction-du-developpement/bibliotheque/php-password/-/tags/1.0.0
