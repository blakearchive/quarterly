#!/usr/bin/env python3
"""
Add new documents to Blake Quarterly.

Usage:
    python add_document.py path/to/document.xml
    python add_document.py path/to/document.html
    python add_document.py --reindex-all
"""
import sys
import argparse
from pathlib import Path

from sqlalchemy.orm import Session
from datetime import datetime

from app.database import SessionLocal, init_db
from app.models import Document
from app.utils import (
    parse_xml_metadata,
    parse_html_metadata,
    parse_filename_metadata,
    get_season_year,
)
from app.config import settings


def add_single_document(file_path: Path, db: Session) -> bool:
    """
    Add or update a single document in the database.

    Args:
        file_path: Path to the XML or HTML file
        db: Database session

    Returns:
        True if successful, False otherwise
    """
    if not file_path.exists():
        print(f"Error: File not found: {file_path}")
        return False

    ext = file_path.suffix.lower()
    if ext not in ['.xml', '.html']:
        print(f"Error: Unsupported file type: {ext}")
        return False

    try:
        # Parse filename metadata
        filename_meta = parse_filename_metadata(file_path.name)
        idno = filename_meta['idno']

        # Check if document already exists
        existing = db.query(Document).filter(Document.idno == idno).first()

        if ext == '.xml':
            # Parse XML metadata
            xml_meta = parse_xml_metadata(file_path)

            # Create relative path
            rel_path = file_path.relative_to(settings.base_dir)

            if existing:
                # Update existing document
                print(f"Updating existing document: {idno}")
                existing.volume = filename_meta['volume']
                existing.issue = filename_meta['issue']
                existing.vol_iss = filename_meta['vol_iss']
                existing.title = xml_meta['title']
                existing.author = xml_meta['author']
                existing.author_last = xml_meta['author_last']
                existing.content_type = xml_meta['content_type']
                existing.full_date = xml_meta['full_date']
                existing.season_year = get_season_year(xml_meta['full_date'])
                existing.xml_path = str(rel_path)
                existing.fulltext = xml_meta['fulltext']

                if xml_meta['date']:
                    try:
                        if len(xml_meta['date']) >= 8:
                            date_str = xml_meta['date'][:8]
                            existing.pub_date = datetime.strptime(date_str, '%Y%m%d').date()
                    except ValueError:
                        pass
            else:
                # Create new document
                print(f"Adding new document: {idno}")
                doc = Document(
                    idno=idno,
                    volume=filename_meta['volume'],
                    issue=filename_meta['issue'],
                    vol_iss=filename_meta['vol_iss'],
                    title=xml_meta['title'],
                    author=xml_meta['author'],
                    author_last=xml_meta['author_last'],
                    content_type=xml_meta['content_type'],
                    full_date=xml_meta['full_date'],
                    season_year=get_season_year(xml_meta['full_date']),
                    format='text/xml',
                    xml_path=str(rel_path),
                    fulltext=xml_meta['fulltext'],
                )

                if xml_meta['date']:
                    try:
                        if len(xml_meta['date']) >= 8:
                            date_str = xml_meta['date'][:8]
                            doc.pub_date = datetime.strptime(date_str, '%Y%m%d').date()
                    except ValueError:
                        pass

                db.add(doc)

        elif ext == '.html':
            # Parse HTML metadata
            html_meta = parse_html_metadata(file_path)

            # Create relative path
            rel_path = file_path.relative_to(settings.base_dir)

            if existing:
                # Update HTML path on existing document
                print(f"Adding HTML version to existing document: {idno}")
                existing.html_path = str(rel_path)
            else:
                # Create new document from HTML
                print(f"Adding new HTML document: {idno}")
                doc = Document(
                    idno=idno,
                    volume=filename_meta['volume'],
                    issue=filename_meta['issue'],
                    vol_iss=filename_meta['vol_iss'],
                    title=html_meta['title'],
                    author=html_meta['author'],
                    author_last=html_meta['author_last'],
                    content_type=html_meta['content_type'],
                    format='text/html',
                    html_path=str(rel_path),
                    fulltext=html_meta['fulltext'],
                )
                db.add(doc)

        db.commit()

        # Update search vector
        db.execute(
            """
            UPDATE documents
            SET search_vector = to_tsvector('english',
                COALESCE(title, '') || ' ' ||
                COALESCE(author, '') || ' ' ||
                COALESCE(fulltext, '')
            )
            WHERE idno = :idno
            """,
            {'idno': idno}
        )
        db.commit()

        print(f"✓ Successfully processed: {file_path.name}")
        return True

    except Exception as e:
        print(f"Error processing {file_path.name}: {e}")
        db.rollback()
        return False


def reindex_all(db: Session):
    """Re-index all documents from docs/ and html/ directories."""
    print("Re-indexing all documents...")

    # Import and run the full indexer
    from app.indexer import index_documents
    index_documents()


def main():
    parser = argparse.ArgumentParser(
        description='Add new documents to Blake Quarterly database'
    )
    parser.add_argument(
        'files',
        nargs='*',
        help='Document files to add (XML or HTML)'
    )
    parser.add_argument(
        '--reindex-all',
        action='store_true',
        help='Re-index all documents from docs/ and html/ directories'
    )

    args = parser.parse_args()

    # Initialize database
    init_db()
    db = SessionLocal()

    try:
        if args.reindex_all:
            reindex_all(db)
        elif args.files:
            success_count = 0
            for file_str in args.files:
                file_path = Path(file_str)
                if add_single_document(file_path, db):
                    success_count += 1

            print(f"\n✓ Successfully processed {success_count}/{len(args.files)} files")
        else:
            parser.print_help()
            return 1

        return 0

    finally:
        db.close()


if __name__ == '__main__':
    sys.exit(main())
