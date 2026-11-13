import fastapi
import pymysql
import pydantic
import os

app = fastapi.FastAPI()

# Define a User model for the login endpoint
class User(pydantic.BaseModel):
    username: str
    password: str
    admin: bool = False

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


# Simple endpoint to test the API
@app.get("/helloworld")
async def read_root():
    return {"message": "Hello World"}



@app.get("/login")
async def login():
    return {"message": "Login endpoint"}

@app.post("/login")
async def login(user: User):
    # Check if the user is in the database and if the password is correct and send back the status of the user
    response = {    
        "status": "error", 
        "message": "Invalid username or password"
    }
    
    bdd = get_db_connection()
    try:
        with bdd.cursor() as cursor:
            sql = "SELECT Email, Pass, Statut FROM Utilisateur WHERE Email=%s AND Pass=%s"
            cursor.execute(sql, (user.username, user.password))
            result = cursor.fetchone()
        
        return result if result else response
    except Exception as e:
        return {"status": "error", "message": str(e)}
    finally:
        bdd.close()
    

    #if user.username == "superuser@test.fr" and user.password == "L@nnion":
    #    response = {"status": "success", "user": user.username, "password": user.password, "admin": True}
    #if user.username == "simpleuser@test.fr" and user.password == "L@nnion":
    #    response = {"status": "success", "user": user.username, "password": user.password, "admin": False}
    #else:
    #    raise fastapi.HTTPException(status_code=401, detail="Invalid username or password")
    
    #return response

