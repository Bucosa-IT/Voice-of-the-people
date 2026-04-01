from pydantic import BaseModel 
from typing import Optional 
from datetime import datetime

class ReadElection(BaseModel):
    id :int 
    name :str
    description :str 
    image_url :str 
    created_at : datetime 
    created_by : str  

class ReadUser(BaseModel):
    id :int 
    name :str 
    password:str 
    reg_number: str 
    created_at : datetime 
    is_admin:bool 
    is_voter:bool

class ReadCandidate(BaseModel):
    id :int 
    name : str 
    description:str
    image_url :str 
    created_by :str 
    created_at :str 

class CreateUser(BaseModel):
    name :str 
    password :str 
    reg_number :str 

class CreateCandidate(BaseModel):
    name :str
    description:str 
    image_url :str | None = None 
    created_by :str 
    to_election :str 

class CreateElection(BaseModel):
    name :str 
    description :str 
    image_url :str | None = None
    created_by: str 

class UpdateUser(BaseModel):
    name :str | None =None
    password :str | None =None
    reg_number :str  | None =None

class UpdateCandidate(BaseModel):
    name :str | None =None
    description:str  | None =None
    image_url :str | None =None 


class UpdateElection(BaseModel):
    name :str | None = None
    description :str  | None = None
    image_url :str | None = None

    




