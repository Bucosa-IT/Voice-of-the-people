from database.settings import get_connection 
from sqlalchemy.orm import Session
from fastapi import APIRouter ,Depends
from admins.models import User 
from admins.serializer import ReadUser,CreateUser 


router =APIRouter(prefix="/Register",tags=["client"])

@router.post("/",response_model=ReadUser)
async def regester_user(user_details :CreateUser,session:Session=Depends(get_connection)):
    regestered_user =User(name=user_details.name, password =user_details.password ,reg_number =user_details.reg_number) 
    session.add(regestered_user)
    session.commit()
    session.refresh(regestered_user)
    session.close()
    return regestered_user