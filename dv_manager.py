# app/db/manager.py
from __future__ import annotations

from dataclasses import dataclass
from enum import Enum
from typing import Dict, Optional

from sqlalchemy import create_engine, text
from sqlalchemy.engine import Engine
from sqlalchemy.orm import sessionmaker, Session

from app.core.config import settings


class DBDriver(str, Enum):
    SQLITE = "sqlite"
    PGVECTOR = "pgvector"  # postgres + pgvector


@dataclass
class DBConnectionConfig:
    name: str
    driver: DBDriver
    url: str
    echo: bool = False


class DatabaseManager:
    """
    Quản lý nhiều connection giống Laravel Database Manager.
    """

    def __init__(self, configs: Dict[str, DBConnectionConfig], default: str):
        self._configs = configs
        self._default = default

        self._engines: Dict[str, Engine] = {}
        self._sessionmakers: Dict[str, sessionmaker] = {}

    # ---------- API công khai ----------

    def get_engine(self, connection: Optional[str] = None) -> Engine:
        name = connection or self._default
        if name in self._engines:
            return self._engines[name]

        if name not in self._configs:
            raise ValueError(f"Unknown DB connection: {name}")

        conf = self._configs[name]
        engine = create_engine(conf.url, echo=conf.echo, future=True)
        self._engines[name] = engine

        # hook cho từng loại driver
        if conf.driver == DBDriver.PGVECTOR:
            self._init_pgvector(engine)

        return engine

    def get_sessionmaker(self, connection: Optional[str] = None) -> sessionmaker:
        name = connection or self._default
        if name in self._sessionmakers:
            return self._sessionmakers[name]

        engine = self.get_engine(name)
        SessionLocal = sessionmaker(
            bind=engine,
            autoflush=False,
            autocommit=False,
            future=True,
        )
        self._sessionmakers[name] = SessionLocal
        return SessionLocal

    def get_session(self, connection: Optional[str] = None) -> Session:
        SessionLocal = self.get_sessionmaker(connection)
        return SessionLocal()

    # ---------- Internal helpers ----------

    @staticmethod
    def _init_pgvector(engine: Engine) -> None:
        """
        Đảm bảo extension pgvector tồn tại.
        Có thể gọi 1 lần khi khởi động app.
        """
        with engine.connect() as conn:
            conn.execute(text("CREATE EXTENSION IF NOT EXISTS vector"))
            conn.commit()


# ---------- Khởi tạo singleton manager ----------

def build_db_manager() -> DatabaseManager:
    configs: Dict[str, DBConnectionConfig] = {
        "sqlite": DBConnectionConfig(
            name="sqlite",
            driver=DBDriver.SQLITE,
            url=settings.DB_SQLITE_URL,
            echo=settings.DB_ECHO,
        ),
        "pgvector": DBConnectionConfig(
            name="pgvector",
            driver=DBDriver.PGVECTOR,
            url=settings.DB_PGVECTOR_URL,
            echo=settings.DB_ECHO,
        ),
    }

    default_name = settings.DB_DEFAULT
    if default_name not in configs:
        raise RuntimeError(f"Invalid DB_DEFAULT={default_name}")

    return DatabaseManager(configs=configs, default=default_name)


db_manager = build_db_manager()