from database.database import get_connection 
from admins.models import Candidate 
from admins.serializer import UpdateCandidate ,CreateCandidate , ReadCandidate
from sqlalchemy.orm import Session 
from fastapi import APIRouter ,HTTPException ,status ,Depends  


router = APIRouter(prefix="/candidates",tags=["candidates"]) 


@router.get("/",response_model=list[ReadCandidate])
async def get_all_candidates(session:Session =Depends(get_connection)):
    candidates = session.query(Candidate).all() 
    session.close()
    return candidates 

@router.post("/",response_model=ReadCandidate)
async def create_a_candidate(new_candidate : CreateCandidate ,session:Session =Depends(get_connection)):
    created_candidate = Candidate(name=new_candidate.name ,description=new_candidate.description,
                                  image_url = new_candidate.image_url,created_by = new_candidate.created_by ,
                                  to_election =new_candidate.to_election)
    
    session.add(created_candidate)
    session.commit()
    session.refresh(created_candidate)
    session.close() 
    return created_candidate

@router.get("/{candidate_id}",response_model=ReadCandidate)
async def search_a_user(candidate_id:int ,session:Session=Depends(get_connection)):
    candidate = session.query(Candidate).filter(Candidate.id == candidate_id).first()
    if not candidate :
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND , detail="user not found")
    session.close()
    return candidate 

@router.delete("/{candidate_id}",response_model=ReadCandidate)
async def delete_a_candidate(candidate_id:int ,session:Session=Depends(get_connection)):
    deleted_candidate = session.query(Candidate).filter(Candidate.id == candidate_id).first()
    if not deleted_candidate :
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND , detail="user to delete not found ")
    
    session.delete(deleted_candidate)
    session.commit()
    session.close()
    return deleted_candidate 

@router.patch("/{candidate_id}", response_model=ReadCandidate)
async def update_a_candidate(
    candidate_id: int,
    updated_data: UpdateCandidate,
    session: Session = Depends(get_connection)
):
    candidate = session.query(Candidate).filter(Candidate.id == candidate_id).first()
    if not candidate:
        raise HTTPException(status_code=404, detail="Candidate not found")

    for key, value in updated_data.dict(exclude_unset=True).items():
        setattr(candidate, key, value)

    session.commit()
    session.refresh(candidate)
    session.close()
    
    return candidate

