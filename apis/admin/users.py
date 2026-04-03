from fastapi import APIRouter ,Depends , HTTPException ,status 
from admins.models import User 
from admins.serializer import ReadUser ,UpdateUser ,CreateUser 
from database.database import get_connection 
from sqlalchemy.orm import Session 

router =APIRouter( prefix="/users",tags=["users"]) 

@router.get("/",response_model=list[ReadUser]) 
async def get_all_users(session:Session=Depends(get_connection)):
    users = session.query(User).all() 
    session.close()
    return users 

@router.get("/{user_id}",response_model =ReadUser)
async def search_a_user(user_id :int , session:Session =Depends(get_connection)) :
     user = session.query(User).filter(User.id == user_id).first()
     if not user :
          raise HTTPException(status_code=status.HTTP_404_NOT_FOUND ,detail="user not found")
     return user 

@router.delete("/{user_id}",response_model=ReadUser)
async def delete_a_user(user_id:int , session:Session =Depends(get_connection)):
     deleted_user = session.query(User).filter(User.id ==user_id).first()
     if not deleted_user : 
          raise HTTPException(status_code=status.HTTP_404_NOT_FOUND ,detail= "user not found")
     session.delete(deleted_user)
     session.commit()
     session.close()
     return deleted_user 

@router.post("/",response_model=ReadUser)
async def create_a_user(new_user:CreateUser,session:Session= Depends(get_connection)):
      new_created_user = User(name=new_user.name,password=new_user.password,reg_number=new_user.reg_number)
      session.add(new_created_user)
      session.commit()
      session.refresh(new_created_user)
      session.close()
      return new_created_user

@router.patch("/{user_id}",response_model=ReadUser)
async def update_a_user(user_id:int,updated_data:UpdateUser,session:Session=Depends(get_connection)):
     updated_user = session.query(User).filter(User.id == user_id).first()
     if not updated_user : 
          raise HTTPException(status_code= status.HTTP_404_NOT_FOUND,detail="no user found ")
     updated_user.name = updated_data.name 
     updated_user.password = updated_data.password
     updated_user.reg_number = updated_data.reg_number 

     session.commit()
     session.refresh(updated_user)
     session.close()
     return updated_user