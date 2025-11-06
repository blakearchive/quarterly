"""Index XML and HTML documents into database."""
import sys
from pathlib import Path
from datetime import datetime

from sqlalchemy import func
from sqlalchemy.orm import Session

from app.database import SessionLocal, init_db
from app.models import Document
from app.utils import (
    parse_xml_metadata,
    parse_html_metadata,
    parse_filename_metadata,
    get_season_year,
)
from app.config import settings


def index_documents():
    """Index all XML and HTML documents into database."""
    print("Initializing database...")
    init_db()

    db = SessionLocal()

    try:
        # Clear existing documents
        print("Clearing existing documents...")
        db.query(Document).delete()
        db.commit()

        # Index XML documents
        print(f"\nIndexing XML documents from {settings.docs_dir}...")
        xml_count = 0
        xml_files = list(settings.docs_dir.glob("*.xml"))

        for xml_path in xml_files:
            try:
                # Parse filename metadata
                filename_meta = parse_filename_metadata(xml_path.name)

                # Parse XML metadata
                xml_meta = parse_xml_metadata(xml_path)

                # Create relative path
                rel_path = xml_path.relative_to(settings.base_dir)

                # Create document
                doc = Document(
                    idno=filename_meta['idno'],
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

                # Parse date
                if xml_meta['date']:
                    try:
                        if len(xml_meta['date']) >= 8:
                            date_str = xml_meta['date'][:8]
                            doc.pub_date = datetime.strptime(date_str, '%Y%m%d').date()
                    except ValueError:
                        pass

                db.add(doc)
                xml_count += 1

                if xml_count % 50 == 0:
                    print(f"  Indexed {xml_count} XML documents...")

            except Exception as e:
                print(f"  Error indexing {xml_path.name}: {e}")
                continue

        db.commit()
        print(f"✓ Indexed {xml_count} XML documents")

        # Index HTML documents
        print(f"\nIndexing HTML documents from {settings.html_dir}...")
        html_count = 0
        html_files = list(settings.html_dir.glob("*.html"))

        for html_path in html_files:
            try:
                # Parse filename metadata
                filename_meta = parse_filename_metadata(html_path.name)

                # Check if document already exists
                existing = db.query(Document).filter(
                    Document.idno == filename_meta['idno']
                ).first()

                if existing:
                    # Update with HTML path
                    rel_path = html_path.relative_to(settings.base_dir)
                    existing.html_path = str(rel_path)
                else:
                    # Parse HTML metadata
                    html_meta = parse_html_metadata(html_path)

                    # Create relative path
                    rel_path = html_path.relative_to(settings.base_dir)

                    # Create document
                    doc = Document(
                        idno=filename_meta['idno'],
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

                html_count += 1

                if html_count % 50 == 0:
                    print(f"  Indexed {html_count} HTML documents...")

            except Exception as e:
                print(f"  Error indexing {html_path.name}: {e}")
                continue

        db.commit()
        print(f"✓ Indexed {html_count} HTML documents")

        # Create full-text search vectors
        print("\nCreating full-text search vectors...")
        db.execute(
            """
            UPDATE documents
            SET search_vector = to_tsvector('english',
                COALESCE(title, '') || ' ' ||
                COALESCE(author, '') || ' ' ||
                COALESCE(fulltext, '')
            )
            WHERE fulltext IS NOT NULL
            """
        )
        db.commit()
        print("✓ Full-text search vectors created")

        # Print statistics
        print("\n" + "=" * 60)
        print("INDEXING COMPLETE")
        print("=" * 60)

        total = db.query(Document).count()
        print(f"Total documents: {total}")

        by_type = db.query(
            Document.content_type,
            func.count(Document.id)
        ).group_by(Document.content_type).all()

        print("\nDocuments by type:")
        for content_type, count in sorted(by_type, key=lambda x: x[1], reverse=True):
            type_name = content_type or "Unknown"
            print(f"  {type_name}: {count}")

        volumes = db.query(
            func.min(Document.volume),
            func.max(Document.volume)
        ).first()
        print(f"\nVolume range: {volumes[0]} - {volumes[1]}")

    except Exception as e:
        print(f"Error during indexing: {e}")
        db.rollback()
        raise
    finally:
        db.close()


if __name__ == "__main__":
    index_documents()
