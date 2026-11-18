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
        print(f"LOG : {response}")
        print(f"LOG : {result}")
    else:
        raise fastapi.HTTPException(status_code=401, detail="Invalid username or password")        
    
    return response

