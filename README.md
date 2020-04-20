# Exercice PHP

## Objectif:

Vous devez réaliser une commande en PHP qui prend en paramètre, un fichier CSV et génère un JSON.

Lors d'un live coding sur Twitch (https://www.twitch.tv/fredbouchery), je prendrai tout ou partie des codes réalisés pour les commenter et donner mon avis sur la réalisation qui me plait le plus. En fonction du nombre de réponses, je ferai peut-être plusieurs live.

Pour m'envoyer vos réalisations, contactez moi en DM sur twitter (https://twitter.com/fredbouchery/) avant le jeudi 22 avril 2020 à minuit.

Amusez-vous surtout ! Faites simple et efficace. Libérez-vous de vos chaînes habituelles. Parfois, c'est bon de se faire de gros délires de conception. Par contre, si c'est trop illisible, je risque de ne pas trop regarder.

## Contraintes

- Compatible PHP 7.4
- Aucune dépendances externes (du bon "vanilla")
- Doit proposer des tests unitaires ... sans framework de test (oui, du bon "vanilla" aussi)
- Pas de base de données
- Bien prendre en compte les cas d'erreur (pas les bonnes entrées, fichier mal formé, partiel, etc.)

## Commandes à développer

### csv2json

Commande : `csv2json <nom_fichier> [options]`

Le fichier source est un fichier CSV où la première ligne contient le nom des champs. Le séparateur doit être intelligent (voir explication plus bas).

Attention, les champs peuvent être encadrés par des guillemets.

options:
  * `--fields "<field1,field2,field3,etc"` : liste des champs qui doivent sortir dans le JSON avec un séparateur intelligent (tous par défaut)
  * `--aggregate <field>` : aggrège les données sur un champ
  * `--desc <file>` : fichier de description des types de champ (voir plus bas la description de ce fichier)
  * `--pretty` : le fichier JSON doit être mis en forme pour une lecture facile. Par défaut, tout est sur une seule ligne.
    
le "séparateur intelligent", c'est la capacité de l'application à déterminer quel est le séparateur. Cela peut être un espace, une virgule, un pipe, etc.

L'aggrégation de champ, c'est la capacité à transformer par exemple :
```
name;id;date
foo;5;2020-05-03
foo;9;2020-05-03
bar;1;2020-03-21
boo;4;2020-03-14
foo;12;2020-05-07
boo;5;2020-02-19
far;10;2020-04-30
```
Sans aggrégation:
```json
[
    {"name":"foo", "id": 5, "date": "2020-05-03"},
    {"name":"foo", "id": 9, "date": "2020-05-03"},
    {"name":"bar", "id": 1, "date": "2020-03-21"},
    {"name":"boo", "id": 4, "date": "2020-03-14"},
    {"name":"foo", "id": 12, "date": "2020-05-07"},
    {"name":"boo", "id": 5, "date": "2020-02-19"},
    {"name":"far", "id": 10, "date": "2020-04-30"}
]
``` 

Avec aggrégation sur "name" : 
```json
{
  "foo": [
    {"id": 5, "date": "2020-05-03"},
    {"id": 9, "date": "2020-05-03"},
    {"id": 12, "date": "2020-05-07"}
  ],
  "bar": [
    {"id": 1, "date": "2020-03-21"}
  ],
  "boo": [
    {"id": 4, "date": "2020-03-14"},
    {"id": 5, "date": "2020-02-19"}
  ],
  "far": [
    {"id": 10, "date": "2020-04-30"}
  ]
}
``` 

#### Fichier de description des types

Le fichier est présenté sous la forme d'un fichier texte contenant des "clef = valeur"
```
### Ceci est un commentaire.
# les lignes vide ne sont pas utilisées

name = string  # oui, il peut y avoir des espaces de chaque coté du "="
id=?int
date=datetime
```

Les types supportés sont "string", "int", "integer", "float", "bool", "boolean", "date", "time", "datetime".

Si le type est préfixé par un "?", cela veut dire qu'un champ vide sera transformé en "null", sinon, cela doit retourner une erreur.
 
Le format des champs data/time sont :
  - date : "yyyy-mm-dd"
  - time : "hh:mm:ss"
  - datetime : "yyyy-mm-dd hh:mm:ss"

Le format bool/boolean accepte :
  - false, 0, off, no
  - true, 1, on, yes

### test

Commande : `unit-test`

Cette commande lance les tests unitaires (maisons), en affichant le nombre de tests exécutés et les tests KO. Ci dessous un exemple de sortie :

```
Tests executed: 43

All tests are successful !
```

```
Tests executed: 43

Tests KO: 3
* test-load-corrupt
* test-float-is-not-integer
* test-yes-is-true
```

Vous organisez et réalisez les tests unitaires comme vous voulez. La seule chose demandée, c'est de retourner un "successful" quand tout va bien.
