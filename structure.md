.
├─ apps/
│  ├─ api/                      # FastAPI: điểm vào HTTP
│  │  ├─ main.py
│  │  ├─ routers/               # /sources, /ingest, /search, /summarize, /chat...
│  │  ├─ dependencies/
│  │  ├─ schemas/               # Pydantic: request/response (envelope: success, code, data, meta, errors)
│  │  ├─ services/              # use-cases mỏng gọi xuống packages/*
│  │  ├─ repositories/          # DB/Vector adapters (pg, pgvector)
│  │  └─ core/                  # config, logging, errors, middleware (Tracing, CORS, RateLimit)
│  ├─ worker/                   # Celery/RQ: ingest, OCR, embed, re-index, long jobs
│  │  ├─ tasks/
│  │  └─ core/
│  └─ cli/                      # Typer CLI: batch ingest, admin tools
│
├─ packages/                    # “timeless code” có thể tái dùng/packaging
│  ├─ rag/
│  │  ├─ ingest/                # loaders: pdf, docx, onedrive/sharepoint connectors (tương lai)
│  │  ├─ preprocess/            # ocr, detect lang, clean text, dedupe
│  │  ├─ chunk/                 # chiến lược chia đoạn (by tokens/sentences/semantic)
│  │  ├─ embed/                 # model wrappers, caching
│  │  ├─ index/                 # indexers: pgvector/faiss/qdrant
│  │  ├─ retrieve/              # top-k, hybrid bm25+dense
│  │  ├─ rerank/                # cross-encoder/rankers
│  │  ├─ generate/              # LLM orchestration (tooling, guardrails)
│  │  └─ eval/                  # RAGAS/metrics, golden-set
│  ├─ adapters/
│  │  ├─ storage/               # local/MinIO/S3; versioning; checksum/hash
│  │  ├─ vectorstores/          # PgVector/Faiss interface thống nhất (ports/adapters)
│  │  ├─ llms/                  # OpenAI/Local (với retry, rate-limit, cost tracking)
│  │  └─ secrets/               # providers: env, vault (chỉ code; dữ liệu secrets không ở repo)
│  ├─ common/
│  │  ├─ settings/              # pydantic-settings, .env, profiles (dev/stg/prod)
│  │  ├─ telemetry/             # logging + tracing + metrics (OTel/Prom)
│  │  ├─ utils/                 # idempotency, datetime, io
│  │  └─ exceptions.py
│  └─ sdk/                      # Client SDK
│     ├─ python/                # pip-installable, auto-gen từ OpenAPI
│     └─ js/                    # (tuỳ chọn) fetch/axios client
│
├─ configs/                     # cấu hình tách biệt, có kế thừa
│  ├─ base.yaml
│  ├─ dev.yaml
│  ├─ prod.yaml
│  └─ logging.yaml
│
├─ data/                        # DVC-tracked hoặc .gitignore (không commit raw data)
├─ models/                      # model artifacts (DVC/LFS), card/manifest
├─ notebooks/                   # nghiên cứu, exploratory; tuyệt đối không gọi trực tiếp trong prod
├─ experiments/                 # 2025-10-27-exp-001/ (config, metrics, charts, report.md)
├─ migrations/                  # Alembic
├─ tests/
│  ├─ unit/                     # packages/* tách bạch domain
│  ├─ integration/              # api + db + vector
│  └─ e2e/                      # luồng RAG hoàn chỉnh, golden-set
├─ scripts/                     # một lần chạy: backfill, export, smokecheck
├─ deploy/
│  ├─ docker/
│  │  ├─ api.Dockerfile
│  │  ├─ worker.Dockerfile
│  │  └─ docker-compose.yml     # dev/staging
│  └─ k8s/                      # manifests/helm (prod)
├─ infra/
│  ├─ db/                       # init.sql, extension pgvector
│  └─ terraform/                # (tuỳ chọn) IaC
├─ docs/
│  ├─ prd/ srs/                 # PRD, SRS (AsciiDoc)
│  ├─ adr/                      # quyết định kiến trúc (ADR-001…)
│  ├─ api/                      # OpenAPI, changelog API
│  └─ runbooks/                 # on-call, SLO/SLA, incident guide
├─ .gitlab-ci.yml
├─ pyproject.toml               # poetry/uv; ruff, mypy, pytest config
├─ .pre-commit-config.yaml
├─ Makefile or Taskfile.yml     # lệnh “1-click”: make dev, make test, make ingest…
├─ .env.example
└─ README.md