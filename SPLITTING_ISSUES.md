# Splitting Full-Issue Files into Articles

This guide explains how to process a complete issue (one PDF or HTML file) and split it into individual articles for the Blake Quarterly archive.

## Overview

If you have an entire issue as:
- **One PDF file** - containing all articles in sequence
- **One HTML file** - containing all articles in a single page

You can use the `split_issue.py` tool to:
1. Split it into individual article files
2. Generate XML metadata for each article
3. Automatically add all articles to the database

## Quick Start

### 1. Install Additional Dependencies

```bash
source venv/bin/activate
pip install -r requirements.txt  # Includes PyPDF2, BeautifulSoup4, PyYAML
```

### 2. Create Configuration File

Create a YAML file specifying how to split the issue:

```yaml
source_file: "issue_48_4.pdf"
source_type: "pdf"
volume: 48
issue: 4
date: "20240621"

articles:
  - slug: "toc"
    title: "Table of Contents"
    type: "toc"
    pages: "1-2"

  - slug: "smith-blake"
    title: "Blake's Influence"
    author: "Sarah Smith"
    type: "article"
    pages: "3-15"
```

### 3. Run the Splitter

```bash
python split_issue.py --config my_issue.yaml
```

### 4. Done!

The tool will:
- ✓ Split the PDF/HTML into individual files
- ✓ Create XML metadata files
- ✓ Add all articles to the database
- ✓ Update search indexes

---

## Detailed Guide

### Configuration File Format

The configuration file (YAML format) defines:
- The source file
- Issue metadata
- How to split the content
- Metadata for each article

#### Basic Structure

```yaml
# Source file information
source_file: "path/to/issue.pdf"  # or .html
source_type: "pdf"  # or "html"

# Issue-level metadata
volume: 48
issue: 4
date: "20240621"  # YYYYMMDD format
date_text: "Summer 2024"  # Optional display text

# Articles to extract
articles:
  - slug: "article-identifier"
    title: "Article Title"
    author: "Author Name"
    type: "article"
    # Extraction method (see below)
```

### PDF Splitting

For PDF files, use the `pages` field to specify page ranges:

```yaml
articles:
  - slug: "smith-article"
    title: "Article Title"
    author: "Sarah Smith"
    type: "article"
    pages: "3-15"  # Pages 3 to 15 (inclusive)
    volume: 48
    issue: 4
    date: "20240621"

  - slug: "single-page"
    title: "Single Page Article"
    author: "John Doe"
    type: "news"
    pages: "16"  # Just page 16
    volume: 48
    issue: 4
    date: "20240621"
```

**Notes:**
- Page numbers are 1-indexed (page 1 is the first page)
- Use format "start-end" for ranges (e.g., "3-15")
- Use single number for one page (e.g., "16")
- The tool extracts these pages into separate PDF files

### HTML Splitting

For HTML files, you have several options to identify article boundaries:

#### Option 1: CSS Selector

```yaml
articles:
  - slug: "article1"
    title: "Article Title"
    selector: "article.blake-article"  # CSS selector
    volume: 48
    issue: 4
```

#### Option 2: Element ID

```yaml
articles:
  - slug: "article1"
    title: "Article Title"
    id: "article-blake-influence"  # Element with this ID
    volume: 48
    issue: 4
```

#### Option 3: Text Markers

If your HTML has comment markers:

```html
<!-- ARTICLE: smith-blake START -->
<div>Article content here...</div>
<!-- ARTICLE: smith-blake END -->
```

Use:

```yaml
articles:
  - slug: "smith-blake"
    title: "Article Title"
    start_marker: "<!-- ARTICLE: smith-blake START -->"
    end_marker: "<!-- ARTICLE: smith-blake END -->"
    volume: 48
    issue: 4
```

### Article Metadata Fields

Each article entry supports these fields:

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| `slug` | Yes | Unique identifier | `"smith-blake"` |
| `title` | Yes | Article title | `"Blake's Influence"` |
| `author` | No | Author name | `"Sarah Smith"` |
| `type` | No | Content type (default: article) | `"review"` |
| `volume` | Yes | Volume number | `48` |
| `issue` | Yes | Issue number | `4` |
| `date` | Yes | Publication date (YYYYMMDD) | `"20240621"` |
| `date_text` | No | Display date | `"Summer 2024"` |
| `pages` | PDF only | Page range | `"3-15"` |
| `selector` | HTML only | CSS selector | `"article.main"` |
| `id` | HTML only | Element ID | `"article1"` |
| `start_marker` | HTML only | Start marker text | `"<!-- START -->"` |
| `end_marker` | HTML only | End marker text | `"<!-- END -->"` |

### Content Types

Use these values for the `type` field:

- `article` - Regular article (default)
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

---

## Usage Examples

### Example 1: Split a PDF Issue

Create `issue_48_4.yaml`:

```yaml
source_file: "Blake_Quarterly_48_4.pdf"
source_type: "pdf"
volume: 48
issue: 4
date: "20240621"
date_text: "Summer 2024"

articles:
  - slug: "toc"
    title: "Volume 48, Issue 4 - Table of Contents"
    type: "toc"
    pages: "1-2"
    volume: 48
    issue: 4
    date: "20240621"

  - slug: "smith-influence"
    title: "Blake's Lasting Influence on Modern Poetry"
    author: "Sarah Smith"
    type: "article"
    pages: "3-15"
    volume: 48
    issue: 4
    date: "20240621"

  - slug: "jones-review"
    title: "Review: Blake's Jerusalem, New Critical Edition"
    author: "Michael Jones"
    type: "review"
    pages: "16-22"
    volume: 48
    issue: 4
    date: "20240621"
```

Run:

```bash
python split_issue.py --config issue_48_4.yaml
```

Output:
- `pdf_splits/48.4.toc.pdf`
- `pdf_splits/48.4.smith-influence.pdf`
- `pdf_splits/48.4.jones-review.pdf`
- `docs/48.4.toc.xml`
- `docs/48.4.smith-influence.xml`
- `docs/48.4.jones-review.xml`
- All articles added to database

### Example 2: Split an HTML Issue

Create `issue_48_4_html.yaml`:

```yaml
source_file: "Blake_Quarterly_48_4_Full.html"
source_type: "html"
volume: 48
issue: 4
date: "20240621"

articles:
  - slug: "toc"
    title: "Table of Contents"
    type: "toc"
    selector: "div#toc"
    volume: 48
    issue: 4
    date: "20240621"

  - slug: "smith-influence"
    title: "Blake's Lasting Influence"
    author: "Sarah Smith"
    type: "article"
    id: "article-smith"
    volume: 48
    issue: 4
    date: "20240621"
```

Run:

```bash
python split_issue.py --config issue_48_4_html.yaml
```

Output:
- `html/48.4.toc.html`
- `html/48.4.smith-influence.html`
- `docs/48.4.toc.xml` (metadata)
- `docs/48.4.smith-influence.xml` (metadata)
- All articles added to database

### Example 3: Split Without Adding to Database

Useful for reviewing splits before importing:

```bash
python split_issue.py --config issue.yaml --no-db
```

Review the files, then later:

```bash
python add_document.py --reindex-all
```

---

## Command-Line Options

```bash
python split_issue.py [OPTIONS]

Required:
  --config FILE          YAML configuration file

Optional:
  --pdf FILE            Override PDF source file from config
  --html FILE           Override HTML source file from config
  --output-dir DIR      Override output directory
  --no-db               Skip adding to database
```

### Examples

```bash
# Basic usage
python split_issue.py --config issue.yaml

# Override source file
python split_issue.py --config issue.yaml --pdf different.pdf

# Custom output directory
python split_issue.py --config issue.yaml --output-dir custom/path

# Split but don't add to database yet
python split_issue.py --config issue.yaml --no-db
```

---

## Workflow Guide

### Step-by-Step Process

#### 1. Prepare Your Source File

Ensure you have:
- PDF with all articles in order, OR
- HTML file with all articles

Note the structure:
- Which pages contain which articles (PDF)
- How articles are marked/structured (HTML)

#### 2. Create Configuration File

```bash
# Copy example
cp example_split_config.yaml my_issue.yaml

# Edit with your details
nano my_issue.yaml
```

Fill in:
- Source file path
- Issue metadata (volume, issue, date)
- Article list with page ranges or selectors
- Metadata for each article

#### 3. Test the Split

Run with `--no-db` to test without database changes:

```bash
python split_issue.py --config my_issue.yaml --no-db
```

Review the output files:
- Check PDFs open correctly
- Check HTML files display properly
- Verify XML metadata is correct

#### 4. Run Full Split

```bash
python split_issue.py --config my_issue.yaml
```

#### 5. Verify Results

Check the output:

```bash
# View in browser
python -m app.main
# Visit http://localhost:8000

# Check database
docker exec -it blake_quarterly_db psql -U blake -d blake_quarterly
SELECT idno, title, author FROM documents WHERE volume = 48 AND issue = 4;
```

#### 6. Edit Content (If Needed)

The tool creates XML files with placeholder content:

```xml
<p>[Content from split PDF/HTML - edit this file to add actual content]</p>
```

Edit the XML files in `docs/` to add full article text, then re-index:

```bash
python add_document.py --reindex-all
```

---

## Tips and Best Practices

### For PDF Splitting

1. **Verify page numbers** - Open PDF and note actual page numbers
2. **Account for cover pages** - First content page might be page 2 or 3
3. **Check page ranges** - Make sure ranges don't overlap
4. **Test with one article first** - Verify output before doing full issue

### For HTML Splitting

1. **Inspect the HTML structure** first:
   ```bash
   # View HTML structure
   cat issue.html | grep -E '(id=|class=|<!-- )'
   ```

2. **Use specific selectors** - More specific = more reliable
   - ✓ Good: `article#blake-influence`
   - ✗ Bad: `div`

3. **Add markers if needed** - If HTML lacks structure, add comments:
   ```html
   <!-- ARTICLE: smith-blake START -->
   ...content...
   <!-- ARTICLE: smith-blake END -->
   ```

4. **Preserve formatting** - The tool extracts HTML as-is

### General Tips

1. **One issue at a time** - Don't try to process multiple issues in one config
2. **Consistent naming** - Use slugs like `lastname-keyword` (e.g., `smith-influence`)
3. **Include ToC** - Always extract the Table of Contents as first article
4. **Verify metadata** - Double-check dates, authors, titles before running
5. **Backup first** - Back up database before major imports
6. **Test in development** - Set `ENVIRONMENT=development` in `.env` for testing

---

## Troubleshooting

### PDF splitting fails

**Error:** "PyPDF2 not installed"

**Solution:**
```bash
pip install pypdf2==3.0.1
```

**Error:** "Invalid page range"

**Solution:**
- Check page numbers are 1-indexed (first page = 1)
- Verify PDF actually has those pages
- Use format "start-end" or single number

### HTML splitting produces empty files

**Problem:** Selector doesn't match any elements

**Solution:**
1. Inspect HTML structure:
   ```bash
   grep -n "article\|div" issue.html | head -20
   ```

2. Test selector in browser console:
   ```javascript
   document.querySelectorAll('your-selector')
   ```

3. Try different selector or use markers

### Articles not appearing in database

**Problem:** Files created but not in database

**Solution:**
1. Check if `--no-db` flag was used
2. Manually add:
   ```bash
   python add_document.py --reindex-all
   ```

3. Check logs for errors

### Metadata missing

**Problem:** Articles in database but missing title/author

**Solution:**
1. Check YAML config has all required fields
2. Edit XML files in `docs/` to add metadata
3. Re-index:
   ```bash
   python add_document.py --reindex-all
   ```

---

## Advanced Usage

### Processing Multiple Issues

Create separate config files for each issue:

```bash
# Split all issues
for config in configs/issue_*.yaml; do
    python split_issue.py --config "$config"
done
```

### Batch Processing Script

Create `batch_split.sh`:

```bash
#!/bin/bash
# Batch process multiple issues

ISSUES=(
    "configs/issue_48_1.yaml"
    "configs/issue_48_2.yaml"
    "configs/issue_48_3.yaml"
    "configs/issue_48_4.yaml"
)

for config in "${ISSUES[@]}"; do
    echo "Processing $config..."
    python split_issue.py --config "$config"
    echo "---"
done

echo "All issues processed!"
python add_document.py --reindex-all
```

### Extracting from Scanned PDFs

If your PDF is scanned images (not text), you'll need OCR first:

```bash
# Install OCR tool
sudo apt install tesseract-ocr

# OCR the PDF
ocrmypdf input.pdf output_ocr.pdf

# Then split
python split_issue.py --config issue.yaml --pdf output_ocr.pdf
```

---

## Output Files

The splitting tool creates:

### For PDF Sources
```
pdf_splits/
  48.4.toc.pdf
  48.4.article1.pdf
  48.4.article2.pdf

docs/
  48.4.toc.xml
  48.4.article1.xml
  48.4.article2.xml
```

### For HTML Sources
```
html/
  48.4.toc.html
  48.4.article1.html
  48.4.article2.html

docs/
  48.4.toc.xml
  48.4.article1.xml
  48.4.article2.xml
```

### Database Entries

Each article gets a database entry with:
- Metadata (title, author, date, type)
- File paths (XML and/or HTML)
- Full-text search index
- Volume and issue information

---

## Summary

**To split a full-issue file:**

1. Install dependencies: `pip install -r requirements.txt`
2. Create config file: `cp example_split_config.yaml my_issue.yaml`
3. Edit config with your issue details
4. Run: `python split_issue.py --config my_issue.yaml`
5. Verify: Check files and database

**For detailed examples, see `example_split_config.yaml`**

The tool handles all the complexity of:
- Extracting individual articles
- Creating proper file structure
- Generating XML metadata
- Adding to database
- Updating search indexes

Making it easy to process complete issues!
