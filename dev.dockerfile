# syntax=docker/dockerfile:1.7
# Dockerfile.dev — FastAPI + uv (pyproject.toml) cho môi trường phát triển

FROM python:3.12-slim

# --- Hệ thống & uv ---
ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PATH="/workspace/.venv/bin:$PATH"

# Tuỳ biến UID/GID để tránh lỗi permission khi bind-mount
ARG UID=1000
ARG GID=1000

# Các lib hay dùng khi build wheels (có thể thêm/bớt)
RUN apt-get update && apt-get install -y --no-install-recommends \
      curl ca-certificates git build-essential pkg-config \
      # ví dụ các lib hệ thống phổ biến (tuỳ dự án):
      libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && curl -LsSf https://astral.sh/uv/install.sh | sh \
    && ln -s /root/.local/bin/uv /usr/local/bin/uv \
    && groupadd -g "${GID}" dev || true \
    && useradd -m -u "${UID}" -g "${GID}" dev

WORKDIR /workspace

# --- Cài dependencies từ pyproject (DEV) để cache hiệu quả ---
# Sao chép file metadata trước để tận dụng layer cache
COPY pyproject.toml ./
# Nếu có lockfile sẽ tái lập bản build tốt hơn
COPY uv.lock ./

# Tạo .venv & cài deps ở chế độ DEV
RUN --mount=type=cache,target=/root/.cache/uv \
    uv sync --dev || uv sync --dev

# (Tuỳ chọn) Cài thêm tool dev chung
# RUN --mount=type=cache,target=/root/.cache/uv uv add --dev ruff mypy pytest httpx pre-commit

# Copy toàn bộ mã nguồn (khi dev sẽ bind mount nên lớp này ít dùng)
COPY . .

# Quyền sở hữu cho user dev
RUN chown -R dev:dev /workspace
USER dev

EXPOSE 8000

# Mặc định chạy hot-reload. Đổi module `app.main:app` theo dự án của bạn
CMD ["uv", "run", "uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]