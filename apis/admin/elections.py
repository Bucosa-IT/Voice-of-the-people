from database.database import get_connection 
from sqlalchemy.orm import Session 
from fastapi import APIRouter ,HTTPException ,Depends ,status
from admins.models import (
    Election 
) 
from admins.serializer import( ReadElection, UpdateElection, CreateElection)

router =APIRouter(prefix="/elections",tags=["elections"]) 

# all elctions
@router.get("/",response_model=list[ReadElection])
async def all_elections(session:Session =Depends(get_connection)):
    elections = session.query(Election).all()
    session.close()
    return elections 

# searching by id 
@router.get("/{election_id}",response_model =ReadElection)
async def search_a_election(election_id:int ,session:Session =Depends(get_connection)):
    election = session.query(Election).filter(Election.id == election_id).first() 

    if not election :
        raise HTTPException(status_code= status.HTTP_404_NOT_FOUND , detail="no election of the provided id ")
    session.close()
    return election

# delete a exixting election
@router.delete("/{election_id}",response_model=ReadElection)
async def delete_a_election(election_id:int ,session:Session =Depends(get_connection)):
    election_to_delete = session.query(Election).filter(Election.id == election_id).first() 
    if not election_to_delete :
        raise HTTPException( status_code= status.HTTP_404_NOT_FOUND ,detail="no electin of the provided id") 
    session.delete(election_to_delete)
    session.commit() 
    session.close() 
    return election_to_delete 

# create a new election 
@router.post("/",response_model = ReadElection)
async def adding_a_election(new_election : CreateElection,session:Session = Depends(get_connection)):
    created_election = Election(name = new_election.name ,description = new_election.description,
                                image_url = new_election.image_url,created_by = new_election.created_by)
    session.add(created_election)
    session.commit()
    session.refresh(created_election)
    session.close()
    return created_election 

# update a election 
@router.patch("/{election_id}",response_model=ReadElection)
async def updating_a_election(election_id:int ,updated_data:UpdateElection,session:Session=Depends(get_connection)):
    updated_election = session.query(Election).filter(Election.id == election_id).first()
    if updated_election :
        updated_election.name = updated_data.name 
        updated_election.description = updated_data.description
        updated_election.image_url = updated_data.image_url
    else :
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND,detail ="election to update not found ")
    session.commit()
    session.refresh(updated_election)
    session.close() 
    return updated_election


