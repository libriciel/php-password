# Dictionnaires de mots de passe

Listes utilisées par `Libriciel\Password\Dictionary\FileDictionary` (guide
ANSSI-PG-078, recommandation R27). Chaque fichier contient un mot de passe par
ligne, en minuscules, sans doublon, encodé en UTF-8.

## richelieu-fr.txt

- Source : [tarraschk/richelieu](https://github.com/tarraschk/richelieu),
  fichier `french_passwords_top20000.txt`
- Licence : [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/)
- Contenu : les mots de passe les plus courants des comptes en `.fr` issus de
  fuites de données publiques, sans identifiant ni donnée personnelle
- Modifications : passage en minuscules et suppression des doublons
  (19 343 entrées sur 20 000)

## zxcvbn-en.txt

- Source : [dropbox/zxcvbn](https://github.com/dropbox/zxcvbn), fichier
  `data/passwords.txt`
- Licence : MIT, Copyright (c) 2012-2016 Dan Wheeler and Dropbox, Inc.
- Contenu : les mots de passe, classés par fréquence, de la liste construite
  par zxcvbn à partir du corpus de Mark Burnett
- Modifications : suppression de la colonne de fréquence (47 023 entrées)

## zxcvbn-names.txt

- Source : [dropbox/zxcvbn](https://github.com/dropbox/zxcvbn), fichiers
  `data/male_names.txt`, `data/female_names.txt` et `data/surnames.txt`
- Licence : MIT, Copyright (c) 2012-2016 Dan Wheeler and Dropbox, Inc. (texte
  ci-dessous)
- Contenu : prénoms et noms de famille courants aux États-Unis
- Modifications : conservation des 10 000 premiers noms de famille, fusion,
  suppression des doublons (14 280 entrées)

## insee-prenoms.txt

- Source : INSEE, [Fichier des prénoms](https://www.insee.fr/fr/statistiques/8595130),
  fichier `prenoms-2025-nat_csv.zip` (naissances en France de 1900 à 2025),
  également sur [data.gouv.fr](https://www.data.gouv.fr/datasets/fichier-des-prenoms-depuis-1900)
- Licence : [Licence Ouverte / Open Licence 2.0](https://www.etalab.gouv.fr/licence-ouverte-open-licence/)
- Contenu : les prénoms donnés au moins 100 fois depuis 1900
- Modifications : passage en minuscules, ajout des variantes sans accents et
  sans séparateurs (`andré` → `andre`, `jean-pierre` → `jeanpierre`),
  suppression des doublons (14 430 entrées)

Texte de la licence MIT de zxcvbn (zxcvbn-en.txt et zxcvbn-names.txt) :

```
Copyright (c) 2012-2016 Dan Wheeler and Dropbox, Inc.

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
