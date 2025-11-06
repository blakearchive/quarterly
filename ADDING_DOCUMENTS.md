# Adding New Documents to Blake Quarterly

This guide explains how to add new articles, issues, and other content to the Blake Quarterly archive.

## Quick Start

### Method 1: Add a Single Document

```bash
# Activate virtual environment
source venv/bin/activate

# Add an XML document
python add_document.py docs/48.4.newArticle.xml

# Add an HTML document
python add_document.py html/48.4.newArticle.html

# Add multiple documents at once
python add_document.py docs/48.4.article1.xml docs/48.4.article2.xml html/48.4.article3.html
```

### Method 2: Auto-Discovery (Recommended for Multiple Documents)

```bash
# 1. Add your XML/HTML files to docs/ or html/ directories
cp myArticle.xml docs/48.4.myArticle.xml
cp myArticle.html html/48.4.myArticle.html

# 2. Re-index everything
python add_document.py --reindex-all
```

### Method 3: Manual Re-indexing

```bash
# Add files to docs/ or html/, then:
python -m app.indexer
```

## File Naming Convention

Documents must follow this naming pattern:

### Regular Articles
```
{volume}.{issue}.{slug}.{ext}
```

Examples:
- `48.4.johnson.xml` - Volume 48, Issue 4, article by Johnson
- `1.2.hoover.html` - Volume 1, Issue 2, article by Hoover
- `45.1.review-smith.xml` - Volume 45, Issue 1, review by Smith

### Bonus Content
```
bonus.{slug}.{ext}
```

Examples:
- `bonus.special-article.xml`
- `bonus.essay-2024.html`

### File Extensions
- `.xml` - TEI XML format (preferred for archival)
- `.html` - HTML format

## Document Formats

### XML Format (TEI)

Your XML should follow TEI (Text Encoding Initiative) format:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<TEI xmlns="http://www.tei-c.org/ns/1.0">
    <teiHeader>
        <fileDesc>
            <titleStmt>
                <title>Article Title Here</title>
                <author>Author Name</author>
            </titleStmt>
            <publicationStmt>
                <date value="20240315">March 15, 2024</date>
            </publicationStmt>
        </fileDesc>
    </teiHeader>
    <text>
        <body>
            <div1 type="article">
                <!-- Your content here -->
            </div1>
        </body>
    </text>
</TEI>
```

#### Important XML Elements

- `<title>` - Article title
- `<author>` - Author name
- `<date value="YYYYMMDD">` - Publication date
- `<div1 type="...">` - Content type (see below)

#### Content Types

Use the `type` attribute on `<div1>` to specify content type:

- `article` - Regular article
- `review` - Book review
- `news` - News item
- `poem` - Poetry
- `correction` - Correction or addendum
- `minute` - Minute Particulars
- `query` - Query
- `remembrance` - Remembrance
- `discussion` - Discussion
- `illus` - Illustration
- `toc` - Table of Contents

### HTML Format

```html
<!DOCTYPE html>
<html>
<head>
    <title>Article Title</title>
</head>
<body>
    <h1>Article Title</h1>
    <p class="byline">Author Name</p>

    <!-- Your content here -->

</body>
</html>
```

## Step-by-Step: Adding a New Article

### 1. Prepare Your Document

Create your XML or HTML file following the format above.

Example `docs/48.4.jones-blake.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<TEI xmlns="http://www.tei-c.org/ns/1.0">
    <teiHeader>
        <fileDesc>
            <titleStmt>
                <title>Blake's Influence on Modern Poetry</title>
                <author>Sarah Jones</author>
            </titleStmt>
            <publicationStmt>
                <date value="20240621">June 21, 2024</date>
            </publicationStmt>
        </fileDesc>
    </teiHeader>
    <text>
        <body>
            <div1 type="article">
                <p>This article explores Blake's lasting influence...</p>
            </div1>
        </body>
    </text>
</TEI>
```

### 2. Place the File

```bash
# Copy to docs directory
cp myArticle.xml docs/48.4.jones-blake.xml

# If you have an HTML version too:
cp myArticle.html html/48.4.jones-blake.html
```

### 3. Add to Database

Choose one method:

**Option A: Add specific file**
```bash
source venv/bin/activate
python add_document.py docs/48.4.jones-blake.xml
```

**Option B: Re-index all**
```bash
source venv/bin/activate
python add_document.py --reindex-all
```

### 4. Verify

Visit http://localhost:8000/articles to see your new article in the list.

Search for it at http://localhost:8000/search

## Adding a Complete New Issue

When adding a complete new issue (e.g., Volume 48, Issue 4):

### 1. Create Table of Contents

`docs/48.4.toc.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<TEI xmlns="http://www.tei-c.org/ns/1.0">
    <teiHeader>
        <fileDesc>
            <titleStmt>
                <title>Volume 48, Issue 4 - Table of Contents</title>
            </titleStmt>
            <publicationStmt>
                <date value="20240621">June 21, 2024</date>
            </publicationStmt>
        </fileDesc>
    </teiHeader>
    <text>
        <body>
            <div1 type="toc">
                <list>
                    <item>Article 1 by Author A</item>
                    <item>Article 2 by Author B</item>
                    <!-- etc -->
                </list>
            </div1>
        </body>
    </text>
</TEI>
```

### 2. Add All Articles

Add all article XML/HTML files to docs/ and html/ directories:
- `48.4.article1.xml`
- `48.4.article2.xml`
- `48.4.review1.xml`
- etc.

### 3. Re-index

```bash
source venv/bin/activate
python add_document.py --reindex-all
```

### 4. Update Publication Range (if needed)

If this is a new volume beyond the current max, update `.env`:

```bash
MAX_VOLUME=48
MAX_ISSUE=4
```

Restart the application for changes to take effect.

## Updating Existing Documents

To update an existing document:

1. Edit the XML or HTML file in `docs/` or `html/`
2. Run the add command:
   ```bash
   python add_document.py docs/48.4.article.xml
   ```

The script will automatically update the database entry.

## Removing Documents

To remove a document:

### Option 1: Using Python

```python
from app.database import SessionLocal
from app.models import Document

db = SessionLocal()
doc = db.query(Document).filter(Document.idno == "48.4.article").first()
if doc:
    db.delete(doc)
    db.commit()
db.close()
```

### Option 2: Direct SQL

```bash
docker exec -it blake_quarterly_db psql -U blake -d blake_quarterly

# In psql:
DELETE FROM documents WHERE idno = '48.4.article';
\q
```

### Option 3: Re-index without the file

1. Delete the XML/HTML file from `docs/` or `html/`
2. Run: `python add_document.py --reindex-all`

## Batch Operations

### Add Multiple Files

```bash
# Add all XML files from a directory
python add_document.py docs/48.4.*.xml

# Add specific files
python add_document.py \
    docs/48.4.article1.xml \
    docs/48.4.article2.xml \
    html/48.4.article1.html
```

### Add All New Files in a Directory

```bash
# Copy files to docs/
cp /path/to/new/articles/*.xml docs/

# Re-index all
python add_document.py --reindex-all
```

## Automating with Scripts

Create a bash script for bulk imports:

```bash
#!/bin/bash
# import_volume.sh

VOLUME=$1
ISSUE=$2
SOURCE_DIR=$3

if [ -z "$VOLUME" ] || [ -z "$ISSUE" ] || [ -z "$SOURCE_DIR" ]; then
    echo "Usage: ./import_volume.sh <volume> <issue> <source_directory>"
    exit 1
fi

echo "Importing Volume $VOLUME, Issue $ISSUE from $SOURCE_DIR"

# Copy files
cp "$SOURCE_DIR"/*.xml docs/
cp "$SOURCE_DIR"/*.html html/ 2>/dev/null || true

# Activate venv and re-index
source venv/bin/activate
python add_document.py --reindex-all

echo "Import complete!"
```

Usage:
```bash
chmod +x import_volume.sh
./import_volume.sh 48 4 /path/to/volume48/issue4
```

## Troubleshooting

### Document Not Appearing

1. Check the file exists in `docs/` or `html/`
2. Verify filename format: `volume.issue.slug.ext`
3. Check database:
   ```bash
   docker exec -it blake_quarterly_db psql -U blake -d blake_quarterly -c \
     "SELECT idno, title, author FROM documents WHERE idno LIKE '48.4.%';"
   ```
4. Re-index: `python add_document.py --reindex-all`

### Search Not Finding Document

The search vector might not be updated. Re-index:
```bash
python add_document.py --reindex-all
```

### Metadata Not Parsing

- Check XML is valid TEI format
- Ensure namespace is correct: `xmlns="http://www.tei-c.org/ns/1.0"`
- Verify date format: `YYYYMMDD`
- Check content type in `<div1 type="...">`

### Permission Issues

```bash
# Fix file permissions
chmod 644 docs/*.xml
chmod 644 html/*.html
```

## Best Practices

1. **Always use XML when possible** - Better for archival and preservation
2. **Follow naming conventions strictly** - The system relies on them
3. **Include both XML and HTML** - Provides format flexibility
4. **Test in development first** - Use `ENVIRONMENT=development` in `.env`
5. **Backup before bulk operations** - Export database before major changes
6. **Use version control** - Commit XML/HTML files to git

## Database Backup

Before adding many documents:

```bash
# Backup database
docker exec blake_quarterly_db pg_dump -U blake blake_quarterly > backup.sql

# Restore if needed
docker exec -i blake_quarterly_db psql -U blake blake_quarterly < backup.sql
```

## Summary

The simplest workflow for adding documents:

1. **Create your XML/HTML file** following the naming convention
2. **Copy to `docs/` or `html/` directory**
3. **Run:** `python add_document.py docs/your-file.xml`
4. **Verify:** Check http://localhost:8000/articles

That's it! The system handles everything else automatically.
