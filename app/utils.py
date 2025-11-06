"""Utility functions for Blake Quarterly."""
from datetime import datetime
from pathlib import Path
from typing import Optional
import re

from lxml import etree

from app.config import settings


def is_showable(volume: Optional[int], issue: Optional[int]) -> bool:
    """
    Check if content should be shown based on environment and publication range.

    Args:
        volume: Volume number
        issue: Issue number

    Returns:
        True if content should be shown, False otherwise
    """
    if settings.environment != "production":
        return True

    if volume is None or issue is None:
        return False

    if volume < settings.min_volume or volume > settings.max_volume:
        return False

    if issue < settings.min_issue or issue > settings.max_issue:
        return False

    return True


def get_season_year(date_str: Optional[str]) -> Optional[str]:
    """
    Convert date string to season and year format.

    Args:
        date_str: Date string in format like "20110621"

    Returns:
        Season and year like "Spring 2011"
    """
    if not date_str:
        return None

    try:
        # Parse date string (format: YYYYMMDD)
        if len(date_str) >= 8:
            year = date_str[:4]
            month = int(date_str[4:6])

            # Determine season
            if month in [12, 1, 2]:
                season = "Winter"
            elif month in [3, 4, 5]:
                season = "Spring"
            elif month in [6, 7, 8]:
                season = "Summer"
            else:  # 9, 10, 11
                season = "Fall"

            return f"{season} {year}"
    except (ValueError, IndexError):
        pass

    return None


def transform_xml_to_html(xml_path: Path) -> str:
    """
    Transform XML document to HTML using XSLT.

    Args:
        xml_path: Path to XML file

    Returns:
        Transformed HTML content
    """
    try:
        # Load XML
        xml_doc = etree.parse(str(xml_path))

        # Determine which XSL to use based on filename
        if "bonus" in xml_path.name:
            xsl_file = settings.xsl_dir / "bonus.xsl"
        else:
            xsl_file = settings.xsl_dir / "quarterly.xsl"

        # Load XSL
        if not xsl_file.exists():
            return "<p>XSL transformation file not found.</p>"

        xsl_doc = etree.parse(str(xsl_file))
        transform = etree.XSLT(xsl_doc)

        # Transform
        result = transform(xml_doc)

        return str(result)
    except Exception as e:
        return f"<p>Error transforming XML: {str(e)}</p>"


def parse_xml_metadata(xml_path: Path) -> dict:
    """
    Parse metadata from XML file.

    Args:
        xml_path: Path to XML file

    Returns:
        Dictionary of metadata
    """
    metadata = {
        'title': None,
        'author': None,
        'author_last': None,
        'date': None,
        'full_date': None,
        'content_type': None,
        'fulltext': None,
    }

    try:
        tree = etree.parse(str(xml_path))
        root = tree.getroot()

        # Register namespaces
        ns = {'tei': 'http://www.tei-c.org/ns/1.0'}

        # Extract title
        title_elem = root.find('.//tei:titleStmt/tei:title', ns)
        if title_elem is not None and title_elem.text:
            metadata['title'] = title_elem.text.strip()

        # Extract author
        author_elem = root.find('.//tei:titleStmt/tei:author', ns)
        if author_elem is not None and author_elem.text:
            author_name = author_elem.text.strip()
            metadata['author'] = author_name

            # Extract last name (simple heuristic)
            parts = author_name.split()
            if parts:
                metadata['author_last'] = parts[-1]

        # Extract date
        date_elem = root.find('.//tei:publicationStmt/tei:date', ns)
        if date_elem is not None:
            date_value = date_elem.get('value') or date_elem.text
            if date_value:
                metadata['date'] = date_value.strip()
                metadata['full_date'] = date_value.strip()

        # Extract content type from div element
        div_elem = root.find('.//tei:div1', ns)
        if div_elem is not None:
            content_type = div_elem.get('type')
            if content_type:
                metadata['content_type'] = content_type.strip()

        # Extract full text
        text_content = []
        for elem in root.iter():
            if elem.text:
                text_content.append(elem.text.strip())
            if elem.tail:
                text_content.append(elem.tail.strip())

        metadata['fulltext'] = ' '.join(filter(None, text_content))

    except Exception as e:
        print(f"Error parsing XML metadata from {xml_path}: {e}")

    return metadata


def parse_html_metadata(html_path: Path) -> dict:
    """
    Parse metadata from HTML file.

    Args:
        html_path: Path to HTML file

    Returns:
        Dictionary of metadata
    """
    metadata = {
        'title': None,
        'author': None,
        'author_last': None,
        'date': None,
        'content_type': None,
        'fulltext': None,
    }

    try:
        with open(html_path, 'r', encoding='utf-8') as f:
            content = f.read()

        # Parse HTML
        parser = etree.HTMLParser()
        tree = etree.fromstring(content.encode(), parser)

        # Extract title from meta tags or h1
        title_elem = tree.find('.//title')
        if title_elem is not None and title_elem.text:
            metadata['title'] = title_elem.text.strip()
        else:
            h1_elem = tree.find('.//h1')
            if h1_elem is not None and h1_elem.text:
                metadata['title'] = h1_elem.text.strip()

        # Extract author from meta or byline
        byline = tree.find('.//*[@class="byline"]')
        if byline is not None and byline.text:
            author_name = byline.text.strip()
            metadata['author'] = author_name
            parts = author_name.split()
            if parts:
                metadata['author_last'] = parts[-1]

        # Extract full text
        text_content = tree.xpath('//text()')
        metadata['fulltext'] = ' '.join([t.strip() for t in text_content if t.strip()])

    except Exception as e:
        print(f"Error parsing HTML metadata from {html_path}: {e}")

    return metadata


def parse_filename_metadata(filename: str) -> dict:
    """
    Parse metadata from filename.

    Format: volume.issue.slug.ext or bonus.slug.ext

    Args:
        filename: Document filename

    Returns:
        Dictionary with volume, issue, and idno
    """
    metadata = {
        'volume': None,
        'issue': None,
        'idno': None,
        'vol_iss': None,
    }

    # Remove extension
    name = Path(filename).stem

    # Check if bonus content
    if name.startswith('bonus.'):
        metadata['idno'] = name
        return metadata

    # Parse volume.issue.slug format
    parts = name.split('.')
    if len(parts) >= 3:
        try:
            volume = int(parts[0])
            issue = int(parts[1])
            metadata['volume'] = volume
            metadata['issue'] = issue
            metadata['vol_iss'] = f"{volume}.{issue}"
            metadata['idno'] = name
        except ValueError:
            pass

    if not metadata['idno']:
        metadata['idno'] = name

    return metadata


# Content type definitions
CONTENT_TYPES = {
    'article': 'Article',
    'review': 'Review',
    'news': 'News',
    'poem': 'Poem',
    'correction': 'Correction',
    'minute': 'Minute Particulars',
    'query': 'Query',
    'remembrance': 'Remembrance',
    'discussion': 'Discussion',
    'illus': 'Illustration',
    'toc': 'Table of Contents',
}


def get_content_type_display(content_type: Optional[str]) -> str:
    """Get display name for content type."""
    if not content_type:
        return 'Unknown'
    return CONTENT_TYPES.get(content_type, content_type.title())
