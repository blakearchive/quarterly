#!/usr/bin/env python3
"""
Split a full-issue PDF or HTML file into individual articles.

This tool helps you process a complete issue (one PDF or HTML file)
and split it into individual articles for the database.

Usage:
    python split_issue.py --config issue_config.yaml
    python split_issue.py --pdf issue.pdf --config splits.yaml
    python split_issue.py --html issue.html --config splits.yaml
"""
import sys
import argparse
from pathlib import Path
from typing import List, Dict, Optional
import yaml
from datetime import datetime

try:
    from PyPDF2 import PdfReader, PdfWriter
except ImportError:
    PdfReader = None
    PdfWriter = None

from bs4 import BeautifulSoup
from lxml import etree

from app.database import SessionLocal, init_db
from app.models import Document
from app.utils import get_season_year
from app.config import settings


def split_pdf_by_pages(pdf_path: Path, articles: List[Dict], output_dir: Path) -> List[Path]:
    """
    Split a PDF into multiple PDFs based on page ranges.

    Args:
        pdf_path: Path to the source PDF
        articles: List of article configurations with page ranges
        output_dir: Directory to save split PDFs

    Returns:
        List of created PDF file paths
    """
    if PdfReader is None:
        print("Error: PyPDF2 not installed. Install with: pip install pypdf2")
        sys.exit(1)

    output_dir.mkdir(parents=True, exist_ok=True)
    created_files = []

    try:
        reader = PdfReader(str(pdf_path))
        total_pages = len(reader.pages)

        for article in articles:
            if 'pages' not in article:
                print(f"Warning: No pages specified for article {article.get('slug', 'unknown')}")
                continue

            # Parse page range (e.g., "1-5" or "10-12")
            pages_str = article['pages']
            if '-' in pages_str:
                start, end = pages_str.split('-')
                start_page = int(start) - 1  # 0-indexed
                end_page = int(end)  # inclusive
            else:
                start_page = int(pages_str) - 1
                end_page = start_page + 1

            # Validate page range
            if start_page < 0 or end_page > total_pages:
                print(f"Warning: Invalid page range {pages_str} for {article.get('slug')}")
                continue

            # Create output PDF
            writer = PdfWriter()
            for page_num in range(start_page, end_page):
                writer.add_page(reader.pages[page_num])

            # Generate filename
            slug = article.get('slug', f'article{start_page}')
            volume = article.get('volume')
            issue = article.get('issue')

            if volume and issue:
                filename = f"{volume}.{issue}.{slug}.pdf"
            else:
                filename = f"{slug}.pdf"

            output_path = output_dir / filename

            # Write PDF
            with open(output_path, 'wb') as output_file:
                writer.write(output_file)

            created_files.append(output_path)
            print(f"✓ Created: {output_path}")

    except Exception as e:
        print(f"Error splitting PDF: {e}")
        raise

    return created_files


def split_html_by_sections(html_path: Path, articles: List[Dict], output_dir: Path) -> List[Path]:
    """
    Split an HTML file into multiple HTML files based on selectors or markers.

    Args:
        html_path: Path to the source HTML
        articles: List of article configurations with selectors/markers
        output_dir: Directory to save split HTML files

    Returns:
        List of created HTML file paths
    """
    output_dir.mkdir(parents=True, exist_ok=True)
    created_files = []

    try:
        with open(html_path, 'r', encoding='utf-8') as f:
            html_content = f.read()

        soup = BeautifulSoup(html_content, 'html.parser')

        for article in articles:
            slug = article.get('slug', 'unknown')
            volume = article.get('volume')
            issue = article.get('issue')

            # Generate filename
            if volume and issue:
                filename = f"{volume}.{issue}.{slug}.html"
            else:
                filename = f"{slug}.html"

            output_path = output_dir / filename

            # Extract content based on method
            content = None

            if 'selector' in article:
                # CSS selector method
                elements = soup.select(article['selector'])
                if elements:
                    content = ''.join(str(elem) for elem in elements)

            elif 'start_marker' in article and 'end_marker' in article:
                # Text marker method
                start = article['start_marker']
                end = article['end_marker']

                text = str(soup)
                start_idx = text.find(start)
                end_idx = text.find(end, start_idx)

                if start_idx != -1 and end_idx != -1:
                    content = text[start_idx:end_idx + len(end)]

            elif 'id' in article:
                # Element ID method
                element = soup.find(id=article['id'])
                if element:
                    content = str(element)

            if content:
                # Create minimal HTML document
                html_doc = f"""<!DOCTYPE html>
<html>
<head>
    <title>{article.get('title', slug)}</title>
    <meta charset="UTF-8">
</head>
<body>
{content}
</body>
</html>"""

                with open(output_path, 'w', encoding='utf-8') as f:
                    f.write(html_doc)

                created_files.append(output_path)
                print(f"✓ Created: {output_path}")
            else:
                print(f"Warning: Could not extract content for {slug}")

    except Exception as e:
        print(f"Error splitting HTML: {e}")
        raise

    return created_files


def create_xml_from_metadata(article: Dict, output_dir: Path) -> Optional[Path]:
    """
    Create a TEI XML file from article metadata.

    Args:
        article: Article configuration with metadata
        output_dir: Directory to save XML file

    Returns:
        Path to created XML file or None
    """
    slug = article.get('slug', 'unknown')
    volume = article.get('volume')
    issue = article.get('issue')

    if volume and issue:
        filename = f"{volume}.{issue}.{slug}.xml"
    else:
        filename = f"{slug}.xml"

    output_path = output_dir / filename

    # Create TEI XML structure
    tei_ns = "http://www.tei-c.org/ns/1.0"
    nsmap = {None: tei_ns}

    tei = etree.Element("{%s}TEI" % tei_ns, nsmap=nsmap)

    # Header
    header = etree.SubElement(tei, "{%s}teiHeader" % tei_ns)
    file_desc = etree.SubElement(header, "{%s}fileDesc" % tei_ns)

    # Title statement
    title_stmt = etree.SubElement(file_desc, "{%s}titleStmt" % tei_ns)
    title = etree.SubElement(title_stmt, "{%s}title" % tei_ns)
    title.text = article.get('title', 'Untitled')

    if 'author' in article:
        author = etree.SubElement(title_stmt, "{%s}author" % tei_ns)
        author.text = article['author']

    # Publication statement
    pub_stmt = etree.SubElement(file_desc, "{%s}publicationStmt" % tei_ns)
    publisher = etree.SubElement(pub_stmt, "{%s}publisher" % tei_ns)
    publisher.text = "Blake/An Illustrated Quarterly"

    if 'date' in article:
        date_elem = etree.SubElement(pub_stmt, "{%s}date" % tei_ns)
        date_elem.set('value', article['date'])
        date_elem.text = article.get('date_text', article['date'])

    # Text body
    text = etree.SubElement(tei, "{%s}text" % tei_ns)
    body = etree.SubElement(text, "{%s}body" % tei_ns)
    div = etree.SubElement(body, "{%s}div1" % tei_ns)
    div.set('type', article.get('type', 'article'))

    # Add placeholder content
    head = etree.SubElement(div, "{%s}head" % tei_ns)
    head.text = article.get('title', 'Untitled')

    if 'author' in article:
        byline = etree.SubElement(div, "{%s}byline" % tei_ns)
        byline.text = f"by {article['author']}"

    p = etree.SubElement(div, "{%s}p" % tei_ns)
    p.text = "[Content from split PDF/HTML - edit this file to add actual content]"

    # Write XML file
    tree = etree.ElementTree(tei)
    tree.write(
        str(output_path),
        pretty_print=True,
        xml_declaration=True,
        encoding='UTF-8'
    )

    print(f"✓ Created XML metadata: {output_path}")
    return output_path


def add_articles_to_database(articles: List[Dict], db: SessionLocal):
    """
    Add article metadata to database.

    Args:
        articles: List of article configurations
        db: Database session
    """
    from app.utils import parse_filename_metadata

    added_count = 0

    for article in articles:
        try:
            slug = article.get('slug', 'unknown')
            volume = article.get('volume')
            issue = article.get('issue')

            # Generate idno
            if volume and issue:
                idno = f"{volume}.{issue}.{slug}"
            else:
                idno = slug

            # Check if already exists
            existing = db.query(Document).filter(Document.idno == idno).first()
            if existing:
                print(f"⚠ Document {idno} already exists, skipping")
                continue

            # Create document entry
            doc = Document(
                idno=idno,
                volume=volume,
                issue=issue,
                vol_iss=f"{volume}.{issue}" if volume and issue else None,
                title=article.get('title'),
                author=article.get('author'),
                content_type=article.get('type', 'article'),
                full_date=article.get('date'),
                season_year=get_season_year(article.get('date')),
                format='text/html',  # Default to HTML
            )

            # Parse author last name
            if doc.author:
                parts = doc.author.split()
                if parts:
                    doc.author_last = parts[-1]

            # Parse date
            if article.get('date'):
                try:
                    if len(article['date']) >= 8:
                        date_str = article['date'][:8]
                        doc.pub_date = datetime.strptime(date_str, '%Y%m%d').date()
                except ValueError:
                    pass

            # Set file paths if they exist
            if volume and issue:
                xml_path = settings.docs_dir / f"{volume}.{issue}.{slug}.xml"
                html_path = settings.html_dir / f"{volume}.{issue}.{slug}.html"
            else:
                xml_path = settings.docs_dir / f"{slug}.xml"
                html_path = settings.html_dir / f"{slug}.html"

            if xml_path.exists():
                doc.xml_path = str(xml_path.relative_to(settings.base_dir))
            if html_path.exists():
                doc.html_path = str(html_path.relative_to(settings.base_dir))

            db.add(doc)
            added_count += 1
            print(f"✓ Added to database: {idno}")

        except Exception as e:
            print(f"Error adding article {article.get('slug')}: {e}")
            continue

    db.commit()

    # Update search vectors
    if added_count > 0:
        print(f"\nUpdating search vectors...")
        db.execute(
            """
            UPDATE documents
            SET search_vector = to_tsvector('english',
                COALESCE(title, '') || ' ' ||
                COALESCE(author, '') || ' ' ||
                COALESCE(fulltext, '')
            )
            WHERE search_vector IS NULL
            """
        )
        db.commit()
        print(f"✓ Added {added_count} articles to database")


def main():
    parser = argparse.ArgumentParser(
        description='Split a full-issue PDF or HTML into individual articles'
    )
    parser.add_argument(
        '--config',
        required=True,
        help='YAML configuration file specifying how to split the issue'
    )
    parser.add_argument(
        '--pdf',
        help='Path to PDF file (overrides config)'
    )
    parser.add_argument(
        '--html',
        help='Path to HTML file (overrides config)'
    )
    parser.add_argument(
        '--output-dir',
        help='Output directory for split files (default: same as source type)'
    )
    parser.add_argument(
        '--no-db',
        action='store_true',
        help='Do not add articles to database'
    )

    args = parser.parse_args()

    # Load configuration
    config_path = Path(args.config)
    if not config_path.exists():
        print(f"Error: Configuration file not found: {config_path}")
        return 1

    with open(config_path, 'r') as f:
        config = yaml.safe_load(f)

    # Get source file
    if args.pdf:
        source_file = Path(args.pdf)
        source_type = 'pdf'
    elif args.html:
        source_file = Path(args.html)
        source_type = 'html'
    elif 'source_file' in config:
        source_file = Path(config['source_file'])
        source_type = config.get('source_type', 'html' if source_file.suffix == '.html' else 'pdf')
    else:
        print("Error: No source file specified")
        return 1

    if not source_file.exists():
        print(f"Error: Source file not found: {source_file}")
        return 1

    # Get output directory
    if args.output_dir:
        output_dir = Path(args.output_dir)
    elif source_type == 'pdf':
        output_dir = settings.base_dir / 'pdf_splits'
    else:
        output_dir = settings.html_dir

    # Get articles configuration
    articles = config.get('articles', [])
    if not articles:
        print("Error: No articles specified in configuration")
        return 1

    print(f"Processing {source_file} ({source_type})...")
    print(f"Output directory: {output_dir}")
    print(f"Articles to extract: {len(articles)}\n")

    # Split the file
    created_files = []

    if source_type == 'pdf':
        created_files = split_pdf_by_pages(source_file, articles, output_dir)

        # Also create XML metadata files
        xml_dir = settings.docs_dir
        for article in articles:
            create_xml_from_metadata(article, xml_dir)

    else:  # HTML
        created_files = split_html_by_sections(source_file, articles, output_dir)

        # Also create XML metadata files
        xml_dir = settings.docs_dir
        for article in articles:
            create_xml_from_metadata(article, xml_dir)

    print(f"\n✓ Created {len(created_files)} files")

    # Add to database
    if not args.no_db:
        print("\nAdding articles to database...")
        init_db()
        db = SessionLocal()
        try:
            add_articles_to_database(articles, db)
        finally:
            db.close()
    else:
        print("\nSkipping database import (--no-db flag)")

    print("\n" + "=" * 60)
    print("SPLIT COMPLETE")
    print("=" * 60)
    print(f"\nNext steps:")
    print(f"1. Review the split files in {output_dir}")
    print(f"2. Edit XML files in {settings.docs_dir} to add full content")
    print(f"3. Run: python add_document.py --reindex-all")

    return 0


if __name__ == '__main__':
    sys.exit(main())
