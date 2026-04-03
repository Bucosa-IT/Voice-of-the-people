from fastapi import FastAPI 
from database.settings import engine ,Model 

from apis.admin.elections import router as election_router 
from apis.admin.users import router as user_router 
from apis.admin.candidates import router as candidate_router 
from auth.client.registration import router as client_create
from auth.client.authentication import router as auth_client
from auth.admin.authentication import router as auth_admin
from admins.models import (User ,Candidate,Election ,SessionModel)

app = FastAPI()
app.include_router(election_router , prefix="/admin")
app.include_router(user_router, prefix="/admin")
app.include_router(candidate_router ,prefix="/admin") 
app.include_router(auth_admin,prefix="/admin")

app.include_router(client_create ,prefix="/client")
app.include_router(auth_client , prefix="/client")

def main():
    Model.metadata.create_all(engine) 


if __name__ == "main":
    main() 