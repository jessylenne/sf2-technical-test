# Github's Comments

## Installation
### Docker
Vous pouvez lancer le projet en utilisant docker avec la commande suivante:
```sh
docker run -d -p 80:80 -p 3306:3306 jessylenne/g6lamp
```

Puis ouvrez votre navigateur sur http://localhost

> Si la page ne s'affiche pas, c'est sans doute que le serveur virtuel n'as pas réussi à se placer sur localhost:80, retrouvez alors l'adresse IP à mettre dans le navigateur avec la commande
```sh
docker-machine ip
```
> En général ce sera http://192.168.99.100/

### Installation manuelle

- Clonez ce dépot sur votre ordinateur ou directement sur un serveur contenant php
- Sur windows:
    - Lancez dev.bat qui executera php (ainsi que gulp et browser-sync qui sont facultatifs)
    - si browser-sync n'est pas installé, ouvrir manuellement l'URL "localhost:8888" dans votre navigateur
    - Si php ne fonctionne pas, vérifiez qu'il est bien présent dans le PATH de windows
- Sans dev.bat, ou sur une autre plateforme:
    - Avec SQLite
        - Acceder via un terminal au repertoire /public et lancer
```sh
$ php -S localhost:8888
```
-    -
        - accedez via votre navigateur à localhost:8888
        - OU placer les fichiers du projet sur un serveur contenant PHP (>=5.3), et ouvrir l'URL correspondante dans votre navigateur
    - Avec SQL
         - Créer une base de données dédiée
         - Y importer /install.sql
         - Modifier le fichier /.env en spécifiant comme driver "mysql", et en corrigeant si besoin le reste des champs de la section "database"

## Développement
### Prérequis
Installez les dépendances requises
```sh
$ composer install
$ npm install
```
Vous aurez également besoin de phpunit s'il n'est pas déjà installé
### Modification
Sur windows, il vous suffit de lancer dev.bat qui lancera pour vous browser-sync et
```sh
$ gulp watch
```

Gulp se charge du lintage JS, concaténation et modification JS/SASS et des tests PHPUnit