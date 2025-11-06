#!/bin/bash
# Setup script for Blake Quarterly

set -e

echo "========================================"
echo "Blake Quarterly Setup"
echo "========================================"
echo ""

# Check Python version
echo "Checking Python version..."
python3 --version

# Create virtual environment
echo ""
echo "Creating virtual environment..."
python3 -m venv venv

# Activate virtual environment
source venv/bin/activate

# Install dependencies
echo ""
echo "Installing Python dependencies..."
pip install --upgrade pip
pip install -r requirements.txt

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo ""
    echo "Creating .env file from template..."
    cp .env.example .env
    echo "✓ .env file created. You can customize it if needed."
fi

# Start database
echo ""
echo "Starting PostgreSQL database with Docker..."
docker-compose up -d

echo ""
echo "Waiting for database to be ready..."
sleep 15

# Index documents
echo ""
echo "Indexing documents..."
python -m app.indexer

echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "To start the application, run:"
echo "  ./run.sh"
echo ""
echo "Or manually:"
echo "  source venv/bin/activate"
echo "  python -m app.main"
echo ""
echo "Access the application at:"
echo "  http://localhost:8000"
echo ""
