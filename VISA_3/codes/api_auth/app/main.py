import fastapi

app = fastapi.FastAPI()

@app.get("/helloworld")
async def read_root():
    return {"Hello": "World"}

@app.get("/login")
async def login():
    return {"message": "Login endpoint"}