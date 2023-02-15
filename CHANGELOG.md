# 3.0.1 - 2023-02-15

- Fix PasswordStrengthMeterAnssi::entropy() qui renvoyait un string à la place d'un float

# 3.0.0 - 2022-02-24

## Evolution

- La version 3 est basée sur PHP 8.1

## Suppression

- Retrait de la commande symfony de génération de mot de passe (non utilisable dans le programme qui importe la lib)

# 2.0.1 - 2021-12-29

## Supression

- supression de la dépendance paragonie/random_compat (pas de support de PHP 5)

# 2.0.0 - 2021-12-29

## Ajout
- Fork du projet à partir de cbuffin-php-password

## Evolution

- La commande de génération de mot de passe utilise la console Symfony

## Suppression

- Le calcul de hash de mot de passe doit être basé sur la fonction password_hash
