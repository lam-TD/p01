# app/db/dependencies.py

from __future__ import annotations

from functools import partial
from typing import Generator, Callable

from sqlalchemy.orm import Session

from app.db.manager import db_manager


def get_db(connection: str = "default") -> Generator[Session, None, None]:
    """
    Dependency generic cho FastAPI.

    - connection = "default"  -> dùng DB_DEFAULT trong config (vd: sqlite)
    - connection = "sqlite"   -> ép dùng connection sqlite
    - connection = "pgvector" -> ép dùng connection pgvector
    """
    # None -> để DatabaseManager tự dùng default
    conn_name = None if connection == "default" else connection

    db = db_manager.get_session(conn_name)
    try:
        yield db
    finally:
        db.close()


def use_connection(connection: str) -> Callable[[], Generator[Session, None, None]]:
    """
    Helper tạo dependency cho 1 connection cố định.

    Ví dụ dùng trong route:
        from fastapi import Depends

        @router.get("/vector-docs")
        def list_vector_docs(db: Session = Depends(use_connection("pgvector"))):
            ...

    """
    return partial(get_db, connection=connection)


# Một số alias tiện dụng (nếu thích dùng luôn):

def get_default_db() -> Generator[Session, None, None]:
    """
    Dùng DB mặc định (settings.DB_DEFAULT).
    """
    yield from get_db("default")


def get_sqlite_db() -> Generator[Session, None, None]:
    """
    Ép dùng connection 'sqlite'.
    """
    yield from get_db("sqlite")


def get_pgvector_db() -> Generator[Session, None, None]:
    """
    Ép dùng connection 'pgvector'.
    """
    yield from get_db("pgvector")