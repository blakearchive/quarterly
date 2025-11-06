"""Application configuration."""
import os
from pathlib import Path
from typing import Literal

try:
    from pydantic_settings import BaseSettings
except ImportError:
    from pydantic import BaseSettings


class Settings(BaseSettings):
    """Application settings."""

    # Application
    app_name: str = "Blake Quarterly"
    environment: Literal["production", "development", "local"] = "local"
    debug: bool = True

    # Database
    database_url: str = "postgresql://blake:blake@localhost:5432/blake_quarterly"

    # Paths
    base_dir: Path = Path(__file__).parent.parent
    docs_dir: Path = base_dir / "docs"
    html_dir: Path = base_dir / "html"
    xsl_dir: Path = base_dir / "xsl"

    # Publication range
    min_volume: int = 1
    max_volume: int = 48
    min_issue: int = 1
    max_issue: int = 4

    # Server
    host: str = "0.0.0.0"
    port: int = 8000

    class Config:
        env_file = ".env"
        case_sensitive = False


settings = Settings()
