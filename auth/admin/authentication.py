from admins.models import User ,SessionModel 
from database.settings import get_connection
from sqlalchemy.orm import Session 
from sqlalchemy import select 
from fastapi import APIRouter ,Depends , status ,HTTPException ,Response ,Cookie
from admins.serializer import LoginRequest
from datetime import datetime , timedelta 
import uuid 


router = APIRouter(prefix="/Authentication",tags=["admin"]) 

@router.post("/login")
async def admin_login(
    data :LoginRequest,
    response :Response ,
    session:Session =Depends(get_connection),
) :
   

    stmt = select(User).where(User.name == data.username, User.is_admin)
    result = session.execute(stmt)
    admin = result.scalar_one_or_none()

    if not admin or data.password != data.password.strip() :
        raise HTTPException(status_code= status.HTTP_401_UNAUTHORIZED ,detail="only admins are allow ")
    
    session_id = str(uuid.uuid4())

    expires_at = datetime.utcnow() + timedelta(hours=2) 

    admin_session = SessionModel(
          id = session_id,
          user_id = admin.id ,
          expires_at = expires_at 
    ) 
    session.add(admin_session)
    session.commit() 

    response.set_cookie(
        key="session_id",
        value= session_id,
        httponly=True ,
        secure=False 

    )
    return {"Message":"Admin logged in"}


@router.post("/logout")
async def logout_admin(
    response:Response ,session :Session = Depends(get_connection),session_id :str | None = Cookie(default=None)):
    
    if not session_id :
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED , detail="Not logged in "
        )
    
    stmt = select(SessionModel).where(SessionModel.id == session_id)
    result = session.execute(stmt)
    admin_session = result.scalar_one_or_none()

    if admin_session :
        session.delete(admin_session)
        session.commit()

    response.delete_cookie("session_id")

    return {"Message": "Logged out Succesfull "}



def get_current_admin(
    session: Session = Depends(get_connection),
    session_id: str | None = Cookie(default=None)
):
    if not session_id:
        raise HTTPException(status_code=401, detail="Not authenticated")

    stmt = select(SessionModel).where(SessionModel.id == session_id)
    db_session = session.execute(stmt).scalar_one_or_none()

    if not db_session:
        raise HTTPException(status_code=401, detail="Invalid session")

    if db_session.expires_at < datetime.utcnow():
        raise HTTPException(status_code=401, detail="Session expired")

    stmt = select(User).where(User.id == db_session.user_id)
    admin = session.execute(stmt).scalar_one_or_none()

    if not admin or not admin.is_admin:
        raise HTTPException(status_code=403, detail="Admins only")

    return admin