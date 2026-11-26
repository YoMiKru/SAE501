import fastapi
import pymysql
import pydantic
import os

import uuid
import re
import unicodedata

from io import BytesIO
from pathlib import Path
from PIL import Image

app = fastapi.FastAPI()

class Product(pydantic.BaseModel):
    name: str
    price: float
    bio: bool
    producteur: str
    image_path: str

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

@app.get("/helloworld")
async def read_root():
    return {"Hello": "World"}

@app.get("/products")
async def read_root(ville: str = "*"):
    # Renvoie tous les produits si la ville est "*", sinon renvoie les produits de la ville spécifiée
    # Renvoie sous d'un dictioonnaire avec un status et les produits ou un message d'erreur
    # La liste des produits est sous forme d'une liste de dictionnaires qui est dans le dictionnaire de réponse sous la clé "products"
    try:
        bdd = get_db_connection()
        with bdd.cursor() as cursor:
            sql = """
                SELECT *
                FROM Produit
                INNER JOIN Producteur
                ON Produit.NoProducteur = Producteur.NoProducteur
            """

            if ville != "*":
                sql += " WHERE Producteur.Ville = %s"
                cursor.execute(sql, (ville,))
            else:
                cursor.execute(sql)

            result: list[dict] = cursor.fetchall()

            # print(f"LOG : {result}")

        if not result:
            response = {"status": "Error", "message": "Erreur lors de la récupération des produits"}
        else:
            response = {"status": "Success", "products": result}
    except Exception as e:
        response = {"status": "Error", "message": str(e)}
    finally:
        bdd.close()

    return response


# Fonction d'insertion de produit
@app.post("/products")
async def read_root(
    product_name: str = fastapi.Form(...),
    price: float = fastapi.Form(...),
    bio: bool = fastapi.Form(...),
    producteur: str = fastapi.Form(...),
    producteur_nom: str = fastapi.Form(None),
    producteur_prenom: str = fastapi.Form(None),
    producteur_ville: str = fastapi.Form(None),
    image: fastapi.UploadFile = fastapi.File(None)
):

    try:
        bdd = get_db_connection()

        with bdd.cursor() as cursor:

            # Gestion du producteur
            if producteur == "nouveau":

                if not producteur_nom or not producteur_prenom or not producteur_ville:
                    return {
                        "status": "Error",
                        "message": "Informations du producteur manquantes"
                    }

                sql = """
                    INSERT INTO Producteur (Nom, Prenom, Ville)
                    VALUES (%s, %s, %s)
                """

                cursor.execute(sql, (
                    producteur_nom,
                    producteur_prenom,
                    producteur_ville
                ))

                # Récupération de l'identifiant du nouveau producteur
                producteur_id = cursor.lastrowid

            else:
                producteur_id = int(producteur)

                # Vérification de l'existence du producteur
                cursor.execute(
                    "SELECT NoProducteur FROM Producteur WHERE NoProducteur = %s",
                    (producteur_id,)
                )

                if cursor.fetchone() is None:
                    return {
                        "status": "Error",
                        "message": "Producteur introuvable"
                    }

            # Gestion de l'image
            image_path = None

            if image and image.filename:

                # Lecture du fichier
                contenu = await image.read()

                # Vérification de la taille (5 Mo maximum)
                if len(contenu) > 5 * 1024 * 1024:
                    return {
                        "status": "Error",
                        "message": "Image trop volumineuse"
                    }

                # Vérification du format réel de l'image
                from PIL import Image
                from io import BytesIO

                try:
                    img = Image.open(BytesIO(contenu))
                    img.verify()
                    extension = {
                        "JPEG": "jpg",
                        "PNG": "png",
                        "GIF": "gif",
                        "WEBP": "webp"
                    }[img.format]
                except Exception:
                    return {
                        "status": "Error",
                        "message": "Image invalide ou format non autorisé"
                    }

                # Création d'un nom de fichier unique
                import uuid
                import re
                import unicodedata
                from pathlib import Path

                nom_image = unicodedata.normalize("NFKD", product_name)
                nom_image = nom_image.encode("ascii", "ignore").decode("ascii")
                nom_image = re.sub(r"[^a-zA-Z0-9]+", "_", nom_image)
                nom_image = nom_image.strip("_").lower()

                nom_fichier = f"{nom_image}_{uuid.uuid4().hex[:8]}.{extension}"

                # Dossier de stockage des images
                dossier_image = Path("/var/www/html/assets/images")
                dossier_image.mkdir(parents=True, exist_ok=True)

                # Enregistrement de l'image
                chemin_image = dossier_image / nom_fichier
                chemin_image.write_bytes(contenu)

                # Chemin enregistré en BDD
                image_path = f"/assets/images/{nom_fichier}"

            # Insertion du produit
            sql = """
                INSERT INTO Produit
                    (Nom, PrixKilo, Bio, NoProducteur, ImagePath)
                VALUES
                    (%s, %s, %s, %s, %s)
            """

            cursor.execute(sql, (
                product_name,
                price,
                bio,
                producteur_id,
                image_path
            ))

            # Récupération de l'identifiant du produit
            product_id = cursor.lastrowid

        bdd.commit()

        response = {
            "status": "Success",
            "message": "Produit ajouté avec succès",
            "product_id": product_id,
            "image_path": image_path
        }

    except Exception as e:
        if 'bdd' in locals():
            bdd.rollback()

        response = {
            "status": "Error",
            "message": str(e)
        }

        print(str(e))

    finally:
        if 'bdd' in locals():
            bdd.close()

    return response


# Fonction de modification non implémenté
@app.patch("/products/{id}")
async def read_root():
    return {"Hello": "World"}

@app.delete("/products/{id}")
async def read_root(id:int):
    try:
        bdd = get_db_connection()
        with bdd.cursor() as cursor:
            sql = f"DELETE * FROM Produit WHERE id = {id}"
            cursor.execute(sql)

        response = {"status":"Success","message":f"le produit {id} a été supprimé"}

    except Exception as e:
        response = {"status":"Error","message":"Suppression impossible"}
    finally:
        bdd.close()
        
    return response