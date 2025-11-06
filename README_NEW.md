# Blake Quarterly - Modern Python Application

This is the modernized version of Blake/An Illustrated Quarterly digital archive, migrated from PHP to Python with FastAPI.

## Overview

**What Changed:**
- ❌ PHP 8.4 → ✅ Python 3.9+ with FastAPI
- ❌ Apache Solr → ✅ PostgreSQL full-text search
- ❌ Procedural PHP → ✅ Modern async Python framework
- ❌ Mixed templating → ✅ Jinja2 templates
- ❌ jQuery 1.6.1 → ✅ Modern vanilla JavaScript (minimal)
- ❌ Complex setup → ✅ Simple pip + docker-compose

**What Stayed:**
- ✅ All XML/HTML documents (file-based storage)
- ✅ XSLT transformations (using Python lxml)
- ✅ All features (issues, articles, illustrations, search)
- ✅ Same content and functionality

## Technology Stack

- **Backend:** Python 3.9+ with FastAPI
- **Database:** PostgreSQL 15+ with full-text search
- **Templates:** Jinja2
- **XML Processing:** lxml with XSLT support
- **Server:** Uvicorn (ASGI)

## Prerequisites

- Python 3.9 or higher
- Docker and Docker Compose (for PostgreSQL)
- pip (Python package manager)

## Quick Start

### 1. Install Dependencies

```bash
# Create virtual environment
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install Python packages
pip install -r requirements.txt
```

### 2. Start Database

```bash
# Start PostgreSQL using Docker
docker-compose up -d

# Wait for database to be ready (about 10 seconds)
sleep 10
```

### 3. Index Documents

```bash
# Index all XML and HTML documents into database
python -m app.indexer
```

This will:
- Create database tables
- Parse all XML files from `docs/` directory
- Parse all HTML files from `html/` directory
- Create full-text search indexes
- Display indexing statistics

### 4. Run Application

```bash
# Start the FastAPI server
python -m app.main
```

Or use uvicorn directly:

```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

### 5. Access Application

Open your browser to:
- **Application:** http://localhost:8000
- **API Docs:** http://localhost:8000/docs (automatic FastAPI documentation)

## Project Structure

```
quarterly/
├── app/
│   ├── __init__.py
│   ├── main.py              # FastAPI application & routes
│   ├── config.py            # Configuration settings
│   ├── database.py          # Database setup
│   ├── models.py            # SQLAlchemy models
│   ├── utils.py             # Utility functions
│   ├── indexer.py           # Document indexing script
│   └── templates/           # Jinja2 HTML templates
│       ├── base.html
│       ├── index.html
│       ├── articles.html
│       ├── illustrations.html
│       ├── search.html
│       ├── document.html
│       └── about.html
├── docs/                    # XML documents (unchanged)
├── html/                    # HTML documents (unchanged)
├── xsl/                     # XSLT stylesheets (unchanged)
├── js/                      # JavaScript files (unchanged)
├── images/                  # Images (unchanged)
├── style.css               # Main stylesheet (unchanged)
├── requirements.txt        # Python dependencies
├── docker-compose.yml      # PostgreSQL setup
├── .env.example           # Environment variables template
└── README_NEW.md          # This file
```

## Configuration

### Environment Variables

Copy `.env.example` to `.env` and customize:

```bash
cp .env.example .env
```

Available settings:

- `ENVIRONMENT`: `local`, `development`, or `production`
- `DEBUG`: Enable debug mode (`True` or `False`)
- `DATABASE_URL`: PostgreSQL connection string
- `HOST`: Server host (default: `0.0.0.0`)
- `PORT`: Server port (default: `8000`)
- `MIN_VOLUME`, `MAX_VOLUME`: Publication range
- `MIN_ISSUE`, `MAX_ISSUE`: Issue range

### Environment-Specific Behavior

- **production:** Only shows published content within volume/issue range
- **development/local:** Shows all content including unpublished

## Features

### 1. Issue Archive (`/`)
Browse all issues grouped by decade with links to HTML and XML versions.

### 2. Articles Index (`/articles`)
Searchable index of all articles with sorting by:
- Author (last name)
- Title
- Date
- Content type

### 3. Illustrations Index (`/illustrations`)
Browse all illustrations with links to content.

### 4. Search (`/search`)
Full-text search powered by PostgreSQL with:
- Search fields: Full text, Title, Author
- Content type filtering
- Sorting: Relevance, Date, Author
- Pagination (20 results per page)

### 5. Document Viewer
- `/hdoc/{idno}`: View HTML documents
- `/xdoc/{idno}`: View XML documents (XSLT transformed)

## Database Schema

### Documents Table

```sql
CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    idno VARCHAR(255) UNIQUE NOT NULL,
    volume INTEGER,
    issue INTEGER,
    vol_iss VARCHAR(50),
    title TEXT,
    author TEXT,
    author_last VARCHAR(255),
    content_type VARCHAR(100),
    pub_date DATE,
    full_date VARCHAR(50),
    season_year VARCHAR(50),
    format VARCHAR(50),
    xml_path VARCHAR(500),
    html_path VARCHAR(500),
    fulltext TEXT,
    search_vector TSVECTOR
);

-- Indexes
CREATE INDEX idx_search_vector ON documents USING gin(search_vector);
CREATE INDEX idx_author_last ON documents(author_last);
CREATE INDEX idx_content_type ON documents(content_type);
CREATE INDEX idx_pub_date ON documents(pub_date);
```

## Development

### Re-indexing Documents

If you add or modify XML/HTML files:

```bash
python -m app.indexer
```

### Database Management

```bash
# Connect to database
docker exec -it blake_quarterly_db psql -U blake -d blake_quarterly

# Stop database
docker-compose down

# Reset database (delete all data)
docker-compose down -v
docker-compose up -d
python -m app.indexer
```

### Running Tests

```bash
# TODO: Add tests
pytest
```

## Deployment

### Production Setup

1. Update `.env` with production settings:
   ```
   ENVIRONMENT=production
   DEBUG=False
   DATABASE_URL=postgresql://user:pass@prod-host:5432/dbname
   ```

2. Use a production ASGI server:
   ```bash
   pip install gunicorn
   gunicorn app.main:app -w 4 -k uvicorn.workers.UvicornWorker
   ```

3. Use a reverse proxy (Nginx/Apache) in front of the application.

4. Set up SSL/TLS certificates.

### systemd Service Example

```ini
[Unit]
Description=Blake Quarterly FastAPI Application
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/quarterly
Environment="PATH=/var/www/quarterly/venv/bin"
ExecStart=/var/www/quarterly/venv/bin/gunicorn app.main:app -w 4 -k uvicorn.workers.UvicornWorker --bind 0.0.0.0:8000

[Install]
WantedBy=multi-user.target
```

## Maintenance

### Simple Architecture Benefits

1. **One Language:** Python for everything (no PHP/JavaScript split)
2. **One Database:** PostgreSQL (no separate Solr service)
3. **Clear Structure:** Modern MVC-like pattern
4. **Easy Dependencies:** Just `pip install -r requirements.txt`
5. **Self-Documenting:** FastAPI automatically generates API docs
6. **Type Hints:** Python type hints for better code quality
7. **Async Support:** Built-in async/await for performance

### Common Tasks

**Add a new route:**
Edit `app/main.py` and add a new function decorated with `@app.get()`.

**Modify templates:**
Edit files in `app/templates/`. Changes are reflected immediately in debug mode.

**Change database schema:**
1. Modify `app/models.py`
2. Re-run indexer: `python -m app.indexer`

**Update search logic:**
Edit the `search()` function in `app/main.py`.

## Troubleshooting

### Database Connection Issues

```bash
# Check if PostgreSQL is running
docker ps | grep blake_quarterly_db

# View logs
docker logs blake_quarterly_db

# Restart database
docker-compose restart
```

### Import Errors

```bash
# Make sure virtual environment is activated
source venv/bin/activate

# Reinstall dependencies
pip install -r requirements.txt
```

### Port Already in Use

```bash
# Change port in .env file
PORT=8001

# Or specify when running
uvicorn app.main:app --port 8001
```

## Migration Notes

### What Was Removed

- PHP code and includes (`include/`, `*.php` files)
- Solr configuration and index files
- Composer PHP dependencies
- Apache `.htaccess` files (if any)

### What to Keep

- All XML documents in `docs/`
- All HTML documents in `html/`
- All XSL stylesheets in `xsl/`
- Images, JavaScript, and CSS files

### Backward Compatibility

URLs remain mostly the same:
- `/` → Issue archive
- `/articles` → Articles index
- `/illustrations` → Illustrations index
- `/search` → Search
- `/hdoc/{idno}` → HTML document viewer
- `/xdoc/{idno}` → XML document viewer

## Support

For issues or questions:
1. Check this README
2. View API documentation at http://localhost:8000/docs
3. Check application logs
4. Review FastAPI documentation: https://fastapi.tiangolo.com

## License

Same as original Blake Quarterly project.
