# BUT3 R&T : SAE 5.01

## Développer et déployer un microservice dans un environnement virtualisé

<span style="font-size: 1.6em; color: gray;"><em>Compte rendu — VISA_2</em></span>

## Contexte

Le VISA_3 reprend le projet préparé lors du VISA_2. Cette version consiste à ajouter une API python qui permettra de réaliser les opérations CRUD sur au moins une table de données. 

Pour rappel, l'acronime **CRUD** désigne **CREATE** (Créer), **READ** (Lire), **UPDATE** (Mettre à jour) et **DELETE** (Supprimer). 

Pour se faire, on va utiliser la librairie Python FastAPI qui, comme son nom l'indique, de créer rapidement une API. 

## Partie III : Mise en place d'une API CRUD en Python

Avant de directement adapter les opérations CRUD et de migrer tout le système, on va créer deux nouveaux services : api_auth et api_products. 

Le premier va nous servir de gérer l'authentification et le second pour l'ajout, la lecture,  modification et la suprression de données dans le tableau avec les produits. 

Cette séparations permet de conserver une architecture propre : chaque tableau à sa propre API et permet également d'une meilleure sécurisation puisque les API sont indépendantes de chacun.

### III.1. Création des services API à vide

Avant de vouloir tout faire en même temps et de faire des bêtises, on va y aller étape par étape avec la première qui consiste à créer les deux nouveaux services. 

Pour se faire, on développe l'architecture et on ajoute les dossiers et fichiers de configuration correspondants : 

```text
SAE501/
├── images/
├── raw/
├── VISA_1/
├── VISA_2/
│
└── VISA_3/
    └── codes/
        ├── api_auth/               <-- Nouveau dossier
        │   ├── app/
        │   │   └── main.py
        │   ├── Dockerfile
        │   └── requirements.txt
        │
        ├── api_products/           <-- Nouveau dossier
        │   ├── app/
        │   │   └── main.py
        │   ├── Dockerfile
        │   └── requirements.txt
        │
        ├── mariadb/
        │
        ├── web/
        │
        └── docker-compose.yaml
```

Création du **Dockerfile** : 

Les deux services sont des services "miroirs", ils n'ont pas le même rôle mais on le même fonctionnement. Ils ont donc la même configuration. 

- Image de départ basé sur **Python** : python:3.11.15-slim-trixie
- utilisation de dépendances : **fastapi** et **uvicorn**. Ils sont notés dans le fichier **requirments.txt**
- Installation des dépendances à partir du fichier requirments.txt
- Par mesure d'autonomie du conteneur, on **copie le répertoire app/**
- On expose l'utilisation du **port 8001**, utilisé pour communiquer avec l'API. 
- On lance **uvicorn** avec les paramètres correspondant qui va executer le programme situé dans le répertoire. 

On retrouve donc le Dockerfile suivant : 

```Dockerfile
FROM python:3.11.15-slim-trixie

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app/ .

EXPOSE 8001

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001"]
```

Maintenant que la configuration du Dockerfile a été fait, on va ajouter les deux services dans le fichier **docker-compose.yaml** qui se trouve à la racine de la version du projet. 

> Pour rappel : 
>
> Le fichier docker-compose.yaml permet de centraliser la configuration et l'orchestration de l'ensemble des conteneurs d'un projet Docker.
>
>Sans Docker Compose, il faudrait exécuter manuellement les différentes commandes docker build, docker run et configurer individuellement les réseaux, volumes, variables d'environnement et dépendances entre conteneurs.
>
>Avec Docker Compose, toute cette configuration est déclarée dans un seul fichier YAML. Il devient alors possible de démarrer ou d'arrêter l'ensemble des services avec une simple commande (docker compose up -d ou docker compose down).
>
>Cela facilite le déploiement, la maintenance et la reproductibilité de l'environnement, tout en garantissant une configuration cohérente entre les différents services.


On retrouve la configuration suivante : 


```yaml
services:
  [...]
  api_auth:
    build: ./api_auth
    container_name: api_auth
    restart: unless-stopped
    depends_on:
      - mariadb
    volumes:
      - ./api_auth/app:/app
    networks:
      - app_network

  api_products:
    build: ./api_products
    container_name: api_products
    restart: unless-stopped
    depends_on:
      - mariadb
    volumes:
      - ./api_products/app:/app
    networks:
      - app_network
```

Service ``api_auth`` : <br>
- build: ./api_auth : Construit l'image Docker à partir du Dockerfile situé dans le dossier api_auth.
- container_name: api_auth : Attribue le nom api_auth au conteneur.
- restart: unless-stopped : Assure le redémarrage automatique du conteneur en cas d'arrêt, sauf intervention manuelle.
- depends_on: - mariadb : Définit MariaDB comme dépendance, afin qu'il soit démarré avant l'API.
- volumes: ./api_auth/app:/app : Monte le dossier contenant le code source local dans le répertoire /app du conteneur.
- networks: - app_network : Connecte le service au réseau Docker app_network, permettant la communication avec les autres conteneurs du réseau.

Service ``api_products`` : <br>
Les paramètres sont identiques à ceux de api_auth, mais s'appliquent à l'API de gestion des produits, avec son propre Dockerfile et son propre répertoire de code source (./api_products/app).

### III.2. Test de fonctionnement avec retour simple

Pour vérifier que les services fonctionnent correctement, on va ajouter un petit programme dans le fichier main.py avec l'API pour qu'il renvoit un simple JSON contenant un "Hello World". 

```python
import fastapi

app = fastapi.FastAPI()

@app.get("/")
async def read_root():
    return {"Hello": "World"}
```

Le programme définit une route HTTP GET à la racine (/), associée à la fonction asynchrone read_root().
Lorsqu'un client accède à cette route, l'API lui retourne une réponse au format JSON contenant {"Hello": "World"}.

On va désormais vérifier le fonctionnement. On fait les tests depuis le conteneurs web : 

```bash
root@87263dc7b061:/var/www/html# curl http://api_auth:8001
{"Hello":"World"}
root@87263dc7b061:/var/www/html# curl http://api_products:8001
{"Hello":"World"}
```

La résolution de nom se fait par Docker, le nom des conteneurs permettent la connexion entre conteneur. Et comme on peut l'observer, on obtient une réponse. 

On peut également voir les requêtes dans les logs des services API : 
```bash
admin@debian-12-iut:~/SAE501/VISA_3/codes$ sudo docker compose logs api_auth
[sudo] Mot de passe de admin :
api_auth  | INFO:     Started server process [1]
api_auth  | INFO:     Waiting for application startup.
api_auth  | INFO:     Application startup complete.
api_auth  | INFO:     Uvicorn running on http://0.0.0.0:8001 (Press CTRL+C to quit)
api_auth  | INFO:     172.18.0.4:52756 - "GET / HTTP/1.1" 200 OK
```

```bash
admin@debian-12-iut:~/SAE501/VISA_3/codes$ sudo docker compose logs api_products
api_products  | INFO:     Started server process [1]
api_products  | INFO:     Waiting for application startup.
api_products  | INFO:     Application startup complete.
api_products  | INFO:     Uvicorn running on http://0.0.0.0:8001 (Press CTRL+C to quit)
api_products  | INFO:     172.18.0.4:56410 - "GET / HTTP/1.1" 200 OK
```

On a donc la confirmation que la connexion API fonctionne. La prochaine étape est de programmer l'API pour qu'elle réponde au cahier des charges. 

### III.3. Configuration de l'API d'authentification

**INFORMATION : J'ai comme habitude de commenter dans mes codes, la plupart du temps je mélange l'anglais et le français. Les commentaires en anglais sont aussi ajoutées à la main :)**

Pour qu'on puisse utiliser les variables d'environnement, on va déjà les ajouter ! <br>
On ajoute les lignes de la section "environment". Elles correspondent aux variables configurées plus haut dans le fichier pour le service MariaDB.  

```yaml
  ############################################
  api_auth:
    build: ./api_auth
    container_name: api_auth
    restart: unless-stopped
    
    environment:
      DB_HOST: mariadb
      DB_PORT: "3306"
      DB_NAME: BDD_VISA2
      DB_USER: mariadb_user
      DB_PASSWORD: "05102006"

    depends_on:
      - mariadb
    volumes:
      - ./api_auth/app:/app
    networks:
      - app_network

  ############################################
  api_products:
    build: ./api_products
    container_name: api_products
    restart: unless-stopped

    environment:
      DB_HOST: mariadb
      DB_PORT: "3306"
      DB_NAME: BDD_VISA2
      DB_USER: mariadb_user
      DB_PASSWORD: "05102006"

    depends_on:
      - mariadb
    volumes:
      - ./api_products/app:/app
    networks:
      - app_network
```

Une fois que c'est fait, on va pouvoir développer l'API d'authentification. Pour commencer, on va faire des expérience : 

```python
import fastapi

app = fastapi.FastAPI()

# Simple endpoint to test the API
@app.get("/helloworld")
async def read_root():
    return {"message": "Hello World"}

@app.get("/login")
async def login():
    return {"message": "Login endpoint"}
```

Avec cette nouvelle petite partie, on va tester 1 chose : vérifier les endpoints et le fonctionnement de l'API pour voir si il répond bien ce qu'on lui demande. Si on exécute ces deux commandes, on observe bien qu'on a deux réponses différentes. Cette étape permet de "déclarer" à l'API que on peut avoir des requêtes GET avec tel ou tel point de terminaison. 

```bash
root@fc30c52265f6:/var/www/html# curl http://api_auth:8001/helloworld
{"message":"Hello World"}
```
```bash
root@fc30c52265f6:/var/www/html# curl http://api_auth:8001/login
{"message":"Login endpoint"}
```

On peut voir les logs des requêtes : 

```bash
admin@debian-12-iut:~/SAE501/VISA_3/codes$ sudo docker compose logs api_auth
api_auth  | INFO:     Started server process [1]
api_auth  | INFO:     Waiting for application startup.
api_auth  | INFO:     Application startup complete.
api_auth  | INFO:     Uvicorn running on http://0.0.0.0:8001 (Press CTRL+C to quit)
api_auth  | INFO:     172.18.0.5:39228 - "GET /helloworld HTTP/1.1" 200 OK
api_auth  | INFO:     172.18.0.5:49866 - "GET /login HTTP/1.1" 200 OK
```

Une fois qu'on a bien vérifier que la connectivité se fait, on peut créer une fonction qui va initier la connexion avec la base de donnée MariaDB. Pour se faire on utilise la librairie **pymysql**. 

J'ai déjà essayé d'utiliser la librairie **mariadb** mais l'installation échoue lors du déployement du service due, à ce que j'ai compris, à des erreurs de compilation de code C (la librairie n'étant pas précompilée). La librairie **pymysql** étant créé entièrement en Python fonctionne sans erreurs. On ajoute cette librairie dans le fichier requierments.txt. 

On retrouve donc la fonction suivante : 

```python
def get_db_connection():
    # on récupère les informations de connexion à la base de données depuis les variables d'environnement
    db_host = os.getenv("DB_HOST", "")
    db_port = int(os.getenv("DB_PORT", 3306))
    db_name = os.getenv("DB_NAME", "")
    db_user = os.getenv("DB_USER", "")
    db_password = os.getenv("DB_PASSWORD", "")

    # Create a connection to the MariaDB database
    return pymysql.connect(
        host=db_host,
        port=db_port,
        user=db_user,
        password=db_password,
        database=db_name,
        cursorclass=pymysql.cursors.DictCursor
    )
```

Pour la récupération de variable d'environnement, on utilise la librairie **os**. La librairie faisant déjà partie intégrante de la bibliothèque Python 3.xx, on a pas besoin de l'ajouter dans le fichier requierments.txt. 

On peut désormais créer la dernière partie de cette API : la **méthode POST**. Pour rappel, jusqu'à maintenant on a utilisé la **méthode GET** qui permet de récupérer des informations depuis un serveur, sans modifier ses données. Alors que la méthode POST permet d'envoyer des informations à un serveur, généralement pour créer ou traiter une ressource. Dans notre cas, ce sont les identifiants et mots de passes des utilisateurs. 

Pour commencer, on déclare notre endpoint avec le décorateur @app.post("/login"). On définit également un paramètre user de type User, qui correspond au modèle Pydantic créé précédemment. Celui-ci permet de vérifier et de structurer les données reçues dans la requête.

```python
@app.post("/login")
async def login(user: User):
```

On initialise ensuite un dictionnaire response, qui permet de définir une réponse par défaut en cas d'erreur. On récupère également une connexion à la base de données grâce à la fonction get_db_connection() créée précédemment.

```python
response: dict = {
    "status": "Error",
    "message": "Go see the logs for more information",
}

bdd = get_db_connection()
```

Une fois la connexion établie, on ouvre un curseur afin d'exécuter une requête SQL. Celle-ci recherche dans la table Utilisateur un enregistrement correspondant à l'adresse e-mail et au mot de passe transmis par l'utilisateur. La méthode fetchone() permet ensuite de récupérer le résultat de cette recherche.

```python
try:
    with bdd.cursor() as cursor:
        sql = "SELECT Email, Pass, Statut FROM Utilisateur WHERE Email=%s AND Pass=%s"
        cursor.execute(sql, (user.username, user.password))
        result: dict = cursor.fetchone()
finally:
    bdd.close()
```

On utilise ici des paramètres %s pour transmettre les identifiants à la requête SQL. Cela permet notamment d'éviter de construire directement la requête avec les données reçues de l'utilisateur. Le bloc finally garantit également que la connexion à la base de données sera fermée, même si une erreur survient pendant l'exécution de la requête.

Une fois la recherche effectuée, on vérifie si un résultat a été trouvé. Si c'est le cas, on considère que l'authentification a réussi et on prépare une réponse contenant le statut de connexion, l'adresse e-mail de l'utilisateur ainsi que son statut administrateur.

```python
if result:
    response = {
        "status": "Success",
        "user": result["Email"],
        "admin": result["Statut"] == "admin"
    }
```

Dans le cas contraire, aucun utilisateur correspondant aux identifiants transmis n'a été trouvé. On renvoie alors une erreur HTTP 401, qui indique que l'authentification a échoué.

```python
else:
    raise fastapi.HTTPException(
        status_code=401,
        detail="Invalid username or password"
    )
```

Enfin, si l'authentification a réussi, on retourne le dictionnaire response au client. FastAPI se charge automatiquement de convertir ce dictionnaire en réponse JSON.

```python
return response
```

On a donc la partie, complète, suivante : 

```python
@app.post("/login")
async def login(user: User):
    # Check if the user is in the database and if the password is correct and send back the status of the user
    response:dict = {    
        "status": "Error", 
        "message": "Go see the logs for more information",
    }
    
    bdd = get_db_connection()
    try:
        with bdd.cursor() as cursor:
            sql = "SELECT Email, Pass, Statut FROM Utilisateur WHERE Email=%s AND Pass=%s"
            cursor.execute(sql, (user.username, user.password))
            result:dict = cursor.fetchone()
    
    finally:
        bdd.close()
    
    if result["Email"] == user.username and result["Pass"] == user.password:
        response = {"status": "Success", "user": result["Email"], "admin": result["Statut"] == 1 if result["Statut"] is "admin" else False} 
        # La colonne "Status" du tableau des utilisateurs permet de savoir la hiérarchie de l'utilisateur entre "user" et "admin" 
    elif 
        raise fastapi.HTTPException(status_code=401, detail="Invalid username or password")        
    
    return response
```

Ce qu'il nous reste à dire pour la partie authentification est d'adapter le code PHP du service web. 

### III.4 Adaptation du code d'authentification PHP