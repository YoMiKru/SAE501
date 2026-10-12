# VISA_2 — rangement du code web et connexion MariaDB

## Changements effectués
- Connexion PDO centralisée dans `web/code/src/includes/database.php`.
- Les pages qui lisent ou modifient les données utilisent désormais `getDatabase()`.
- Configuration de connexion fournie au conteneur web par les variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` et `DB_PASSWORD` dans `docker-compose.yaml`.
- Remplacement de l'API SQLite3 par PDO/MySQL dans les pages concernées.
- Correction du nom de base (`BDD_VISA2`) et du nom de table d'authentification (`Utilisateur`) conformément au script SQL MariaDB.
- CSS et JavaScript intégrés dans `add_product.php` et `edit_product.php` déplacés dans des fichiers sous `public/assets/`.

## À vérifier au démarrage
Le script `mariadb/23-ProduitsGourmands.sql` est exécuté automatiquement par l'image MariaDB uniquement lors de l'initialisation d'un répertoire de données vide. Comme le projet monte `mariadb/data` dans `/var/lib/mysql`, modifier le script ou les variables d'environnement ne réinitialise pas une base déjà créée.

Vérifier que la base `BDD_VISA2`, les tables `Produit`, `Producteur`, `Utilisateur` et les droits de `mariadb_user` existent. Dans MariaDB, au besoin :

```sql
SHOW DATABASES;
USE BDD_VISA2;
SHOW TABLES;
SHOW GRANTS FOR 'mariadb_user'@'%';
```

Si les droits sont absents et que le compte existe bien sous cet hôte :

```sql
GRANT ALL PRIVILEGES ON `BDD_VISA2`.* TO 'mariadb_user'@'%';
```

Ne supprimez pas `mariadb/data` sans sauvegarde : cela effacerait les données persistées.

## Identifiants
Les valeurs actuellement présentes dans `docker-compose.yaml` sont reprises de la configuration du projet. Pour un déploiement réel, utilisez des secrets ou un fichier `.env` non versionné plutôt que de conserver des mots de passe dans le dépôt.
