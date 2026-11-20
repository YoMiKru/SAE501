import fastapi
import pymysql
import pydantic
import os

app = fastapi.FastAPI()

class Product(pydantic.BaseModel):
    name: str
    price: float
    bio: bool
    producteur: str
    image_path: str

def fix_json(bad_json: str) -> str:
    """
    Fixes a JSON string by replacing single quotes with double quotes.

    Args:
        bad_json (str): The JSON string to fix.

    Returns:
        str: The fixed JSON string.
    """
    for i in range(len(bad_json)):
        if bad_json[i] == "'":
            bad_json = bad_json[:i] + '"' + bad_json[i+1:]

    return bad_json


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
    try:
        bdd = get_db_connection()
        with bdd.cursor() as cursor:
            sql = "SELECT * FROM Produit WHERE Ville=%s" if ville != "*" else "SELECT * FROM Produit"
            cursor.execute(sql, (ville,) if ville != "*" else ())

            result: list[dict] = cursor.fetchall()

            print(f"LOG : {result}")

        #result = fix_json(str(result))
        #print(f"LOG : {result}")

        if not result:
            response = {"status": "Error", "message": "Erreur lors de la récupération des produits"}
        else:
            response = {"status": "Success", "products": result}
    except Exception as e:
        response = {"status": "Error", "message": str(e)}
    finally:
        bdd.close()

    return response

@app.post("/products")
async def read_root():
    return {"Hello": "World"}

@app.put("/products/{id}")
async def read_root():
    return {"Hello": "World"}

@app.delete("/products/{id}")
async def read_root():
    return {"Hello": "World"}