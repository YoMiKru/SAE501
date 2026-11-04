import FastAPI

if __name__ == "__main__":
    app = FastAPI()

    @app.get("/")
    async def read_root():
        return {"Hello": "World"}

    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)