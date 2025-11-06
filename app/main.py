"""Main FastAPI application for Blake Quarterly."""
from fastapi import FastAPI, Request, Depends, HTTPException
from fastapi.responses import HTMLResponse, FileResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from sqlalchemy.orm import Session
from sqlalchemy import func, or_, and_, desc, asc
from pathlib import Path

from app.config import settings
from app.database import get_db, init_db
from app.models import Document
from app.utils import (
    get_season_year,
    is_showable,
    transform_xml_to_html,
    parse_xml_metadata,
)

# Initialize FastAPI app
app = FastAPI(title=settings.app_name)

# Mount static files
app.mount("/js", StaticFiles(directory="js"), name="js")
app.mount("/style", StaticFiles(directory="."), name="style")
app.mount("/images", StaticFiles(directory="images"), name="images")

# Setup templates
templates = Jinja2Templates(directory="app/templates")

# Add settings to templates context
templates.env.globals["settings"] = settings


@app.on_event("startup")
async def startup_event():
    """Initialize database on startup."""
    init_db()


@app.get("/", response_class=HTMLResponse)
async def index(request: Request, db: Session = Depends(get_db)):
    """Main page - issue archive grouped by decade."""
    # Get all documents grouped by volume and issue
    documents = (
        db.query(Document)
        .filter(Document.content_type == "toc")
        .order_by(desc(Document.volume), desc(Document.issue))
        .all()
    )

    # Filter based on environment
    if settings.environment == "production":
        documents = [doc for doc in documents if is_showable(doc.volume, doc.issue)]

    # Group by decade
    grouped = {}
    for doc in documents:
        if doc.volume:
            decade = (doc.volume // 10) * 10
            if decade not in grouped:
                grouped[decade] = []
            grouped[decade].append(doc)

    return templates.TemplateResponse(
        "index.html",
        {
            "request": request,
            "grouped_documents": grouped,
            "page_title": "Blake/An Illustrated Quarterly",
        },
    )


@app.get("/articles", response_class=HTMLResponse)
async def articles(
    request: Request,
    sort: str = "author",
    db: Session = Depends(get_db),
):
    """Articles index page."""
    # Get all articles, excluding ToC and illustrations
    query = db.query(Document).filter(
        and_(
            Document.content_type != "toc",
            Document.content_type != "illus",
        )
    )

    # Apply sorting
    if sort == "title":
        query = query.order_by(asc(Document.title))
    elif sort == "date":
        query = query.order_by(desc(Document.pub_date))
    elif sort == "type":
        query = query.order_by(asc(Document.content_type), asc(Document.author_last))
    else:  # author
        query = query.order_by(asc(Document.author_last))

    documents = query.all()

    # Filter based on environment
    if settings.environment == "production":
        documents = [
            doc for doc in documents if is_showable(doc.volume, doc.issue)
        ]

    return templates.TemplateResponse(
        "articles.html",
        {
            "request": request,
            "documents": documents,
            "sort": sort,
            "page_title": "Articles Index",
        },
    )


@app.get("/illustrations", response_class=HTMLResponse)
async def illustrations(request: Request, db: Session = Depends(get_db)):
    """Illustrations index page."""
    documents = (
        db.query(Document)
        .filter(Document.content_type == "illus")
        .order_by(desc(Document.pub_date))
        .all()
    )

    # Filter based on environment
    if settings.environment == "production":
        documents = [
            doc for doc in documents if is_showable(doc.volume, doc.issue)
        ]

    return templates.TemplateResponse(
        "illustrations.html",
        {
            "request": request,
            "documents": documents,
            "page_title": "Illustrations Index",
        },
    )


@app.get("/search", response_class=HTMLResponse)
async def search(
    request: Request,
    q: str = "",
    field: str = "fulltext",
    content_type: str = "all",
    sort: str = "relevance",
    page: int = 1,
    db: Session = Depends(get_db),
):
    """Search page with PostgreSQL full-text search."""
    results = []
    total = 0
    per_page = 20

    if q:
        # Build base query
        query = db.query(Document)

        # Apply field filter
        if field == "title":
            search_condition = Document.title.ilike(f"%{q}%")
        elif field == "author":
            search_condition = Document.author.ilike(f"%{q}%")
        else:  # fulltext
            # Use PostgreSQL full-text search
            search_condition = Document.search_vector.op("@@")(
                func.plainto_tsquery("english", q)
            )

        query = query.filter(search_condition)

        # Apply content type filter
        if content_type != "all":
            query = query.filter(Document.content_type == content_type)

        # Get total count
        total = query.count()

        # Apply sorting
        if sort == "date":
            query = query.order_by(desc(Document.pub_date))
        elif sort == "author":
            query = query.order_by(asc(Document.author_last))
        else:  # relevance
            if field == "fulltext":
                query = query.order_by(
                    desc(
                        func.ts_rank(
                            Document.search_vector,
                            func.plainto_tsquery("english", q),
                        )
                    )
                )

        # Apply pagination
        offset = (page - 1) * per_page
        results = query.offset(offset).limit(per_page).all()

        # Filter based on environment
        if settings.environment == "production":
            results = [
                doc for doc in results if is_showable(doc.volume, doc.issue)
            ]
            total = len(results)

    # Calculate pagination
    total_pages = (total + per_page - 1) // per_page

    return templates.TemplateResponse(
        "search.html",
        {
            "request": request,
            "query": q,
            "field": field,
            "content_type": content_type,
            "sort": sort,
            "results": results,
            "total": total,
            "page": page,
            "total_pages": total_pages,
            "per_page": per_page,
            "page_title": "Search",
        },
    )


@app.get("/hdoc/{idno}", response_class=HTMLResponse)
async def view_html_doc(
    request: Request,
    idno: str,
    db: Session = Depends(get_db),
):
    """View HTML document."""
    doc = db.query(Document).filter(Document.idno == idno).first()

    if not doc:
        raise HTTPException(status_code=404, detail="Document not found")

    # Check if showable
    if settings.environment == "production" and not is_showable(doc.volume, doc.issue):
        raise HTTPException(status_code=404, detail="Document not available")

    # Read HTML file
    html_path = settings.base_dir / doc.html_path
    if not html_path.exists():
        raise HTTPException(status_code=404, detail="HTML file not found")

    with open(html_path, "r", encoding="utf-8") as f:
        content = f.read()

    return templates.TemplateResponse(
        "document.html",
        {
            "request": request,
            "document": doc,
            "content": content,
            "page_title": doc.title or "Document",
        },
    )


@app.get("/xdoc/{idno}", response_class=HTMLResponse)
async def view_xml_doc(
    request: Request,
    idno: str,
    db: Session = Depends(get_db),
):
    """View XML document (transformed to HTML via XSLT)."""
    doc = db.query(Document).filter(Document.idno == idno).first()

    if not doc:
        raise HTTPException(status_code=404, detail="Document not found")

    # Check if showable
    if settings.environment == "production" and not is_showable(doc.volume, doc.issue):
        raise HTTPException(status_code=404, detail="Document not available")

    # Read and transform XML file
    xml_path = settings.base_dir / doc.xml_path
    if not xml_path.exists():
        raise HTTPException(status_code=404, detail="XML file not found")

    # Transform XML to HTML using XSLT
    content = transform_xml_to_html(xml_path)

    return templates.TemplateResponse(
        "document.html",
        {
            "request": request,
            "document": doc,
            "content": content,
            "page_title": doc.title or "Document",
        },
    )


@app.get("/about", response_class=HTMLResponse)
async def about(request: Request):
    """About page."""
    return templates.TemplateResponse(
        "about.html",
        {
            "request": request,
            "page_title": "About",
        },
    )


@app.get("/health")
async def health_check():
    """Health check endpoint."""
    return {"status": "ok"}


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(
        "app.main:app",
        host=settings.host,
        port=settings.port,
        reload=settings.debug,
    )
