"""Database models for Blake Quarterly."""
from datetime import date
from typing import Optional

from sqlalchemy import Column, Integer, String, Text, Date, Index, func
from sqlalchemy.dialects.postgresql import TSVECTOR

from app.database import Base


class Document(Base):
    """Document model for storing article metadata."""

    __tablename__ = "documents"

    id = Column(Integer, primary_key=True, index=True)

    # Identifiers
    idno = Column(String(255), unique=True, index=True, nullable=False)
    volume = Column(Integer, index=True)
    issue = Column(Integer, index=True)
    vol_iss = Column(String(50), index=True)  # e.g., "45.1"

    # Content
    title = Column(Text)
    author = Column(Text)
    author_last = Column(String(255), index=True)
    content_type = Column(String(100), index=True)  # article, review, poem, etc.

    # Dates
    pub_date = Column(Date, index=True)
    full_date = Column(String(50))
    season_year = Column(String(50))  # e.g., "Spring 2011"

    # Formats and paths
    format = Column(String(50))  # text/xml or text/html
    xml_path = Column(String(500))
    html_path = Column(String(500))

    # Full text content
    fulltext = Column(Text)

    # Full-text search vector
    search_vector = Column(TSVECTOR)

    __table_args__ = (
        Index('idx_search_vector', 'search_vector', postgresql_using='gin'),
        Index('idx_author_last', 'author_last'),
        Index('idx_content_type', 'content_type'),
        Index('idx_pub_date', 'pub_date'),
    )

    def to_dict(self):
        """Convert model to dictionary."""
        return {
            'id': self.id,
            'idno': self.idno,
            'volume': self.volume,
            'issue': self.issue,
            'vol_iss': self.vol_iss,
            'title': self.title,
            'author': self.author,
            'author_last': self.author_last,
            'content_type': self.content_type,
            'pub_date': self.pub_date.isoformat() if self.pub_date else None,
            'full_date': self.full_date,
            'season_year': self.season_year,
            'format': self.format,
            'xml_path': self.xml_path,
            'html_path': self.html_path,
        }
