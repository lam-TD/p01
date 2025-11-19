# app/db/dependencies.py
from typing import Generator
from functools import partial

from fastapi import Depends
from sqlalchemy.orm import Session

from app.db.manager import db_manager


def get_db(connection: str = "default") -> Generator[Session, None, None]:
    """
    Dependency generic, có thể param connection.
    Với connection="default" sẽ dùng DB_DEFAULT trong config.
    """
    conn_name = None if connection == "default" else connection
    db = db_manager.get_session(conn_name)
    try:
        yield db
    finally:
        db.close()


def use_connection(connection: str):
    """
    Helper để dùng trong Depends cho connection cụ thể.
    Ví dụ: Depends(use