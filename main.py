from fastapi import FastAPI 
from database.database import engine ,Model 

from apis.admin.elections import router as election_router 
from apis.admin.users import router as user_router 
from apis.admin.candidates import router as candidate_router 

from admins.models import (User ,Candidate,Election)

app = FastAPI()
app.include_router(election_router , prefix="/admin")
app.include_router(user_router, prefix="/admin")
app.include_router(candidate_router ,prefix="/admin")

def main():
    Model.metadata.create_all(engine) 


if __name__ == "main":
    main()