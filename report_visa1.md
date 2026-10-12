# BUT3 R&T : SAE 5.01

## Développer et déployer un microservice dans un environnement virtualisé

<span style="font-size: 1.6em; color: gray;"><em>Compte rendu</em></span>

## Contexte

Le projet de SAE501 reprend le projet de SAE23 fait en seconde année de BUT R&T. 

Le projet consistait a créer un site web avec une base de donnée sur différents thèmes pour chaque étudiant. J'ai eu le thème des "Produits Gourmand" et donc la base de donnée SQLite correspondant. 

Le site web qu'il fallait donc faire est la page web vitrine du magazin "Produit Gourmand". 

## Partie 0 : Configuration de la VM et installation des dépendances nécessaires

La VM fonctionne sous Debian 12, sur un serveur Proxmox privé, extérieur de l'IUT.

L'accès à distance via SSH a été activé et **sécurisé** pour l'utilisateur `root`.

Pour se connecter à la VM, nous utilisons un serveur SSH intermédiaire qui nous sert de serveur bastion. Nous nous connectons à la VM en tant que `root`, en passant par ce bastion. La connexion au bastion se fait par authentification à l'aide d'une clé SSH Ed25519. Nous ne nous connectons donc jamais directement à la VM depuis l'extérieur.

Nous avons configuré un utilisateur supplémentaire avec lequel nous effectuerons toutes les opérations de configuration des microservices : `admin`. Il a été ajouté aux groupes `sudo` et `docker`. C'est dans son espace de travail que nous établirons le projet.

Pour l'accès au site, le fonctionnement est le suivant :

- Utilisation du nom de domaine `yomikr.fr`, avec le sous-domaine `sae501.yomikr.fr`.
  - Le site est sécurisé et dispose d'un certificat délivré par Let's Encrypt.
- Chaque VISA disposera de son propre fichier `docker-compose.yaml`.
- Chaque VISA utilisera le VISA précédent comme base de départ.

L'intégralité du projet est disponible sur Github : https://github.com/YoMiKru/SAE501

## Partie VISA_0

Pour la parti du VISA_0, on nous était demandé de rendre de nouveau fonctionnelle le site comme on l'avait fait lors du projet SAE23. 

Par contre, il était très complexe de remettre à 0 la configuration qu'il y avait. On avait utilisé l'application XAMPP et apporté des modifications assez importante dans sa configuration. 

J'ai préféré, directement faire la mise au propre et passer au VISA_1.

## Partie I : Réutilisation du projet SAE23 et mise au propre

Cette partie correspond à la version 1 : PHP et SQLite, avec quelques changements de structure.

Initialement, le projet SAE501 fonctionnait sans virtualisation (Docker). Dans cette nouvelle version, la partie Web sera conteneurisée à l'aide de Docker. SQLite étant une base de données intégrée fonctionnant à partir de fichiers, nous n'avons pas créé de conteneur dédié à la base de données. Le fichier SQLite est rendu accessible au conteneur Web grâce à un volume Docker.

## I.1. Mise au propre du projet

L'architecture du projet SAE23 doit être entièrement revue, car la version précédente présente plusieurs limites :

- Elle est inadaptée à une utilisation dans un environnement conteneurisé.
- Elle a été mal pensée au départ.
- Nous avions des difficultés à comprendre le fonctionnement du site Web.

Pour des raisons de temps, nous n'allons pas **refaire** le projet SAE23 dans cette partie. Nous revoyons uniquement les redirections, les noms des fichiers ainsi que leur emplacement dans l'arborescence.

Nous obtenons donc l'architecture suivante :

```text
VISA_1/
└── codes/
    ├── bdd/
    │   └── initData/
    │       ├── 23-ProduitsGourmands.sql
    │       ├── comptes.sqlite
    │       └── produits.db
    │
    ├── logs/
    │   └── connexion.log
    │
    ├── web/
    │   ├── code/
    │   │   ├── public/
    │   │   │   ├── assets/
    │   │   │   │   ├── css/...
    │   │   │   │   ├── images/...
    │   │   │   │   └── js/...
    │   │   │   │
    │   │   │   ├── pages/
    │   │   │   │   ├── ..._action.php
    │   │   │   │   └── ...php
    │   │   │   │
    │   │   │   └── index.php
    │   │   │
    │   │   └── src/
    │   │       ├── config/
    │   │       ├── controllers/
    │   │       ├── includes/
    │   │       └── models/
    │   │
    │   └── Dockerfile
    │
    └── docker-compose.yaml
```

Cette architecture constituera la nouvelle norme d'organisation de l'arborescence pour l'ensemble du projet SAE501.

Les changements dans le code sont les redirections. Exemple : 

```php
<?php
if (!isset($_SESSION['email'])) {
    header("Location: pages/login.php");
    exit();
}
?>
```

Précédemmnent, les redirections étaient adaptées dans le cas où tout les fichiers .php était dans le même dossier et interprété par XAMPP sur Windows. 

## I.2 Mécanisme du déployement

Comme dit plus tôt, le site web est hébergé sur une VM privée tournant le système d'exploitation utilisé à l'IUT : Debian 12. 

Le déployement sur serveur se fait en deux étapes : 
- utilisation de Github comme sauvegarder et serveur de "synchronisation"
- clonage depuis Github sur la VM. 

Ainsi, les données et, surtout, le développement du service ne peut être perdu. Il y a 3 copie à 3 locations différentes : 
- sur l'ordinateur de développement (PC personnel),
- sur Github (lien à la fin de Partie 0),
- sur la VM du serveur Proxmox privée. 

Sur la VM du serveur, on ordonne un ``git clone`` du Repo Github correspondant et on enchaine les ``git pull`` après chaque commit du projet. 

On vérifie le bon fonctionnement via le lien **sae501.yomikr.fr** qui nous redirige au site web du projet. On peut ainsi tester les différentes fonctionnalitées.  

## I.3 Configuration de Docker

### Dockerfile pour le service Web

Pour le service Web, nous allons nous baser sur l'image `php:8.2-apache`. Elle nous servira de base pour ce service, qui fonctionne avec PHP.

Pour la gestion de la base de données SQLite, nous avons besoin de certains outils dans cet environnement. Nous installons donc les dépendances requises.

Puisqu'il s'agit d'un service Web, nous utiliserons le port par défaut de ce type de service : le port 80.

Nous obtenons donc le Dockerfile suivant :

```Dockerfile
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y libsqlite3-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_sqlite gd \
    && rm -rf /var/lib/apt/lists/*

EXPOSE 80
```

### Le fichier de configuration `docker-compose.yaml`

Ce fichier est un raccourci : au lieu d'exécuter les commandes une par une pour configurer les environnements conteneurisés, nous pouvons renseigner toutes les propriétés et caractéristiques dans un fichier appelé `docker-compose.yaml`. Il permet d'enchaîner la création et la configuration de différents services.

Dans notre cas, nous allons l'utiliser pour le service **Web** du projet. Pour ce service, nous souhaitons :

- Utiliser le fichier `Dockerfile` présent dans `./web`.
- Utiliser le port `8080` de la VM pour communiquer avec le port `80` du conteneur.
- Monter plusieurs volumes dans les différents répertoires utilisés par Apache :
  - Le dossier parent contenant la base de données SQLite.
  - Le dossier parent contenant toutes les pages accessibles depuis Internet.
  - Le dossier parent contenant les fichiers privés.
    - Le code interne est monté dans un répertoire situé en dehors du `DocumentRoot` d'Apache. Il n'est donc pas exposé directement par la configuration standard du serveur Web.
  - Le dossier parent contenant les fichiers de journalisation.

Nous obtenons donc le fichier `docker-compose.yaml` suivant :

```yaml
services:
  web:
    build: ./web
    ports:
      - "8080:80"
    volumes:
      - ./bdd/initData:/var/www/data
      - ./web/code/public:/var/www/html
      - ./web/code/src:/var/www/src
      - ./logs:/var/www/logs
```

## 1.4 Conclusion du VISA_1

Dans cette partie, on a vu le déployement, la mise en place et le bon fonctionnemeent du service. 

On a vu comment mettre en place un service en faisant de la conteuneurisation pour la partie Web du projet tout en maintenant les fonctionnalité avec la "communication" avec la base de données. 

La prochaine étape du projet, le VISA_2, consiste à remplacer la base de donnée SQLite par la base de donnée MariaDB. 