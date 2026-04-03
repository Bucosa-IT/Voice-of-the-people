from fastapi.security import HTTPBasic ,HTTPBasicCredentials 
from fastapi import APIRouter ,Depends ,HTTPException ,status
from sqlalchemy.orm import Session
from database.settings import get_connection
from admins.models import User

router = APIRouter(prefix="/Authentication",tags=["client"])

security = HTTPBasic()  

def authentify_user(
        credantials:HTTPBasicCredentials=Depends(security),
        session:Session = Depends(get_connection)): 
    
    authorised_user = session.query(User).filter(User.name == credantials.username).first() 
    if not authorised_user or authorised_user.reg_number != credantials.password :
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED ,datail ="wrong credatials")
    return authorised_user


@router.get("/")
def get_current_user(user :User =Depends(authentify_user)):

    return {
       "name":user.name ,
       "reg number":user.reg_number
    }
 
