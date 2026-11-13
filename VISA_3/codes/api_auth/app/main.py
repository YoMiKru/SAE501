import fastapi
import mariadb
import pydantic

app = fastapi.FastAPI()

class User(pydantic.BaseModel):
    username: str
    password: str


# Simple endpoint to test the API
@app.get("/helloworld")
async def read_root():
    return {"message": "Hello World"}

@app.get("/login")
async def login():
    return {"message": "Login endpoint"}

@app.post("/login", status_code=200)
async def login(user: User):
    # Check if the user is in the database and if the password is correct and send back the status of the user
    response = {"status": "error", "message": "Invalid username or password"}
    
    if user.username == "superuser@test.fr" and user.password == "L@nnion":
        response = {"status": "success", "user": user.username, "password": user.password, "admin": True}
    if user.username == "simpleuser@test.fr" and user.password == "L@nnion":
        response = {"status": "success", "user": user.username, "password": user.password, "admin": False}

    return response

    