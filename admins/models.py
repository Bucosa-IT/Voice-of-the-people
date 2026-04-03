from sqlalchemy import String ,Integer ,Boolean ,DateTime ,ForeignKey ,func
from sqlalchemy.orm import Mapped ,mapped_column , relationship 
from database.database import Model 
from datetime import datetime ,timezone


class User(Model):
    __tablename__ ="Users"  

    id :Mapped[int] = mapped_column(primary_key=True ,unique=True) 
    name : Mapped[str] = mapped_column(String(64),index=True)
    password:Mapped[str] =mapped_column(String(64),index=True)
    reg_number:Mapped[str]=mapped_column(String(64) ,index=True) 

    is_admin:Mapped[bool]=mapped_column(Boolean ,default=False)
    is_voter:Mapped[bool]=mapped_column(Boolean ,default=False)
    created_at:Mapped[datetime]=mapped_column(DateTime(timezone.utc),server_default=func.now()) 

    elections:Mapped["Election"] = relationship(back_populates="creator")
    candidates:Mapped["Candidate"] = relationship(back_populates="creator")



    def __repr__(self):
        return f"<user {self.name}>"


class Candidate(Model):
    __tablename__="Candidates"

    id : Mapped[int] =mapped_column(primary_key=True ,unique=True)
    name : Mapped[str]=mapped_column(String(64),index=True)
    image_url : Mapped[str]=mapped_column(String(64))
    description:Mapped[str]= mapped_column(String(64),index=True)

    created_by:Mapped[str]= mapped_column(ForeignKey("Users.name"))
    to_election : Mapped[str]=mapped_column(ForeignKey("Elections.name"))
    created_at : Mapped[datetime]= mapped_column(DateTime(timezone.utc),server_default=func.now()) 

    creator :Mapped["User"] = relationship(back_populates="candidates") 
    election :Mapped["Election"] = relationship(back_populates="candidates")
    

    def __repr__(self):
        return f"<candidate {self.name}>"


class Election(Model):
    __tablename__ ="Elections"

    id :Mapped[int]=mapped_column(primary_key=True ,unique=True)
    name:Mapped[int]=mapped_column(String(64),index=True)
    description:Mapped[int] = mapped_column(String(64),index=True) 
    image_url: Mapped[str] =mapped_column(String(64),index=True) 

    created_by:Mapped[str] =mapped_column(ForeignKey("Users.name")) 
    created_at:Mapped[datetime]=mapped_column(DateTime(timezone.utc),server_default=func.now())

    candidates:Mapped[list["Candidate"]] = relationship(back_populates="election") 
    creator:Mapped["User"] = relationship(back_populates="elections")


    def __repr__(self):
        return f"<election {self.name}>"