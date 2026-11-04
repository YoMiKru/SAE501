import fastapi
import uvicorn


app = fastapi.FastAPI()

@app.get("/")
async def read_root():
return {"Hello": "World"}

uvicorn.run(app, host="0.0.0.0", port=8001)