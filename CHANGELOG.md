# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format s'inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/)
et le projet suit le [versionnage sémantique](https://semver.org/lang/fr/).

## [4.0.0] - non publiée

### Modifié

- **Rupture de compatibilité** : `PasswordGeneratorAnssi::generate()` lève une
  `PasswordException` lorsque aucune classe de caractères n'est activée, au lieu de
  renvoyer `false` (ce qui provoquait une `TypeError`) (#1).
- **Rupture de compatibilité** : PHP 8.3 minimum, PHP 8.1 n'étant plus maintenu
  et PHP 8.2 ne l'étant plus après le 31 décembre 2026 ; la CI teste PHP 8.3 à 8.5 (#4).
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
