# EcoGarden - API
___
## Description générale :
Projet du parcours "Développeur d'application PHP Symfony" d'OpenClassrooms. 

Le titre complet du projet est : **Créez une API avec Symfony pour mettre à disposition des données**.
Le sujet du projet est la création, à l'aide de Symfony, de l'API REST d'une application de conseils de jardinage avec appel d'une API de météo externe.

### Fonctionnalités à mettre en place : 
* Création et gestion des utilisateurs
* Création et gestion des conseils de jardinage par un administrateur
* Récupération des informations météo sur une API externe (OpenWeather)
___
## Installation : 
* Clonez le projet. 
* Dans un terminal, placez-vous dans le dossier, utilisez la commande ```composer install``` pour installer les dépendances nécessaires. 
* Créez une base de données avec la commande ```php bin/console doctrine:database:create```, puis ```php bin/console doctrine:migrations:migrate``` pour exécuter les migrations.
* Pour créer quelques données, vous pouvez utiliser les DataFixture présentes dans le code en utilisant la commande ```php bin/console doctrine:fixtures:load```, ou créer un utilisateur directement dans la base de données ou la route createUser (utilisateur avec le role USER) via Postman ou Insomnia par exemple.
* Créez un compte sur l'API OpenWeather pour récupérer une clé.
* Dupliquez le fichier .env en le renommant .env.local et modifiez les variables : DATABASE_URL, JWT_PASSPHRASE et API_WEATHER_KEY pour entrer vos valeurs.
___
## Utilisation : 
* Dans votre IDE, lancez le serveur local avec la commande ```symfony serve```.
* Vous pouvez tester les différentes routes avec Postman ou Insomnia, en n'oubliant pas de renseigner le token dans les routes qui nécessitent la connexion d'un utilisateur.
___
## Outils / logiciels nécessaires
* WAMP, XAMP, LAMP ou équivalent
* Postman, Insomnia ou équivalent
* IDE : VSC, PhpStorm... 
