#!/bin/bash
# Simple script to run Blake Quarterly application

echo "Starting Blake Quarterly..."

# Check if virtual environment exists
if [ ! -d "venv" ]; then
    echo "Virtual environment not found. Creating..."
    python3 -m venv venv
    source venv/bin/activate
    pip install -r requirements.txt
else
    source venv/bin/activate
fi

# Check if database is running
if ! docker ps | grep -q blake_quarterly_db; then
    echo "Starting PostgreSQL database..."
    docker-compose up -d
    echo "Waiting for database to be ready..."
    sleep 10
fi

# Check if database is indexed
if [ "$1" = "--index" ]; then
    echo "Indexing documents..."
    python -m app.indexer
fi

# Start the application
echo "Starting FastAPI server..."
echo "Access the application at: http://localhost:8000"
echo "API documentation at: http://localhost:8000/docs"
echo ""
python -m app.main
