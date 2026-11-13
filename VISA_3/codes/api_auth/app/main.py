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

# Connect to the database
bdd = pymysql.connect(
    host=os.getenv('DB_HOST', 'mariadb'),
    port=int(os.getenv('DB_PORT', 3306)),
    user=os.getenv('DB_USER', 'mariadb_user'),
    password=os.getenv('DB_PASSWORD', ''),
    database=os.getenv('DB_NAME', 'BDD_VISA3'),
    charset='utf8mb4',
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
    response = {"status": "error", "message": "Invalid username or password"}
    
    with bdd.cursor() as cursor:
        sql = "SELECT Email, Pass, Statut FROM Utilisateur WHERE Email=%s AND Pass=%s"
        cursor.execute(sql, (user.username, user.password))
        result = cursor.fetchone()

    return result if result else response

    #if user.username == "superuser@test.fr" and user.password == "L@nnion":
    #    response = {"status": "success", "user": user.username, "password": user.password, "admin": True}
    #if user.username == "simpleuser@test.fr" and user.password == "L@nnion":
    #    response = {"status": "success", "user": user.username, "password": user.password, "admin": False}
    #else:
    #    raise fastapi.HTTPException(status_code=401, detail="Invalid username or password")
    
    #return response

