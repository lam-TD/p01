# Kế hoạch 1 tháng cho dev mới tham gia dự án AI (Python + FastAPI + LangChain + RabbitMQ)

Mục tiêu tổng: sau 4 tuần có thể **đảm nhận task thật** trong dự án, nắm chắc nền tảng Python async, thiết kế API với FastAPI, hàng đợi với RabbitMQ, và build 1 luồng RAG tối thiểu bằng LangChain.

---

## Tổng quan theo tuần

| Tuần | Trọng tâm | Deliverables chính |
|---|---|---|
| 1 | Nền tảng Python & FastAPI | Service API skeleton, healthcheck, 3 endpoint CRUD + validation + error handling + test |
| 2 | RabbitMQ & Worker | Hàng đợi + worker async xử lý job, retry, DLQ, metric cơ bản |
| 3 | LangChain (RAG tối thiểu) | Pipeline ingest → embed → lưu vector → /ask truy vấn & stream kết quả |
| 4 | Tích hợp – Chất lượng – Demo | Hardening (log, auth, rate-limit), benchmark cơ bản, tài liệu & demo cuối kỳ |

> Gợi ý daily: 60–90’ học/đọc – 3–5h code – 30’ viết notes & self-review.

---

## Tuần 1 – Python & FastAPI nền tảng

**Mục tiêu kỹ thuật**
- Python 3.x: `async/await`, typing, pydantic v2, packaging, virtualenv.
- FastAPI: router, dependency injection, validation, exception handler, OpenAPI.
- Chất lượng: `ruff` + `mypy` + `pytest` + `httpx` test API.

**Deliverables**
- Repo `ai-service` với cấu trúc ví dụ:
  ```
  app/
    api/ (routers, deps)
    core/ (config, logging, settings)
    models/ (pydantic schemas)
    services/ (business logic)
    workers/ (để tuần 2 dùng)
  tests/
  pyproject.toml (ruff, mypy, pytest config)
  ```
- Endpoint mẫu:
  - `GET /health`
  - `POST /items`, `GET /items/{id}`, `DELETE /items/{id}`
- Exception mapping chuẩn JSON (trace-id trong response header).
- Test: unit + API (pytest, httpx).

**Checklist đánh giá**
- [ ] Code pass `ruff` (0 lỗi)  
- [ ] `mypy` type coverage ≥ 80%  
- [ ] Test pass, coverage ≥ 70%  
- [ ] 3 endpoint có validation & error chuẩn hóa  
- [ ] OpenAPI hiển thị đủ schema & mô tả ngắn

**KPI tuần 1**
- Thời gian từ clone → run `uvicorn` ≤ 30 phút (onboarding)  
- 95p latency `GET /health` trên máy dev ≤ 50ms  
- ≥ 1 PR merge/ngày (nhỏ – tách bạch)

---

## Tuần 2 – RabbitMQ & Worker xử lý nền

**Mục tiêu kỹ thuật**
- RabbitMQ concepts: exchange, queue, routing key, ack, retry, dead-letter.
- Worker async (gợi ý: `aio-pika` hoặc Celery với broker RabbitMQ).
- Mẫu job: “chunk & chuẩn hóa tài liệu” (chuẩn bị cho RAG tuần 3).

**Deliverables**
- `POST /ingest` → publish message (id tài liệu, source path/url).
- Worker:
  - Nhận message, chunk (tách đoạn), lưu metadata (SQLite/Postgres).
  - Retry có backoff; DLQ cho job lỗi 3 lần.
- Metrics/logging:
  - Số job xử lý, thời gian xử lý, tỉ lệ lỗi (log dạng structured).

**Checklist đánh giá**
- [ ] Queue + exchange cấu hình qua env, có DLX/DLQ  
- [ ] Worker idempotent (job chạy lại không tạo dữ liệu trùng)  
- [ ] Retry có backoff, tối đa N lần (ví dụ 3)  
- [ ] Health endpoint cho worker (ví dụ `/metrics` hoặc log heartbeat)

**KPI tuần 2**
- Throughput tối thiểu: **≥ 30 job/phút** với file nhỏ (dev machine)  
- Tỉ lệ job fail < 1%, job mất mát = 0  
- Thời gian từ publish → done P95 ≤ 3s (file nhỏ, local)

---

## Tuần 3 – LangChain & RAG tối thiểu

**Mục tiêu kỹ thuật**
- LangChain: DocumentLoaders, TextSplitters, Embeddings, VectorStore, Retrieval, Chains. (Có thể dùng FAISS/Chroma local cho nhanh.)
- API `/ask`: nhận câu hỏi, truy vấn vector store, build context, gọi LLM, **stream** kết quả.
- Đánh giá tối thiểu: bộ 10–20 Q&A tự tạo để sanity check.

**Deliverables**
- Pipeline ingest: `ingest -> chunk -> embed -> upsert vector`.
- `/ask` hỗ trợ:
  - top-k retrieval, prompt template cơ bản, trích dẫn nguồn (source).
  - stream token (server-sent events hoặc chunked response).
- Cấu hình model qua env (dễ đổi provider).
- Báo cáo ngắn: accuracy cảm quan trên bộ Q&A nhỏ (vd. ≥ 70%).

**Checklist đánh giá**
- [ ] Vector store hoạt động, có upsert/bulk upsert  
- [ ] `/ask` trả kèm nguồn (citations)  
- [ ] Có prompt template & guardrails đơn giản (ví dụ từ chối ngoài miền dữ liệu)  
- [ ] Log token usage (nếu dùng API tính phí)

**KPI tuần 3**
- Top-k = 4–8, hit-rate trên bộ Q&A mẫu ≥ 70%  
- Latency `/ask` đến **token đầu tiên** ≤ 1.5s (dev)  
- Tỉ lệ câu trả lời kèm ≥1 citation ≥ 90%

---

## Tuần 4 – Tích hợp & Chất lượng & Demo

**Mục tiêu kỹ thuật**
- Bảo mật & vận hành cơ bản: `.env`, secret rotation, CORS, rate limit, input size limit, request ID, access log.
- Quan sát: structured logging, basic tracing, error rate dashboard (tối thiểu log + counter).
- Benchmark & hardening; tài liệu & demo.

**Deliverables**
- Tài liệu:
  - `README` (run, test, kiến trúc, luồng dữ liệu).
  - `API.md` (endpoint, ví dụ request/response).
  - `OPERATIONS.md` (env, queue, retry/DLQ, backup đơn giản).
- Benchmark ngắn:
  - `ab/hey` cho `/ask` & `/ingest` với số liệu P50/P95.
- Demo cuối: ingest 100 tài liệu nhỏ & hỏi 5 câu thực tế.

**Checklist đánh giá**
- [ ] Rate limiting cơ bản (vd. 60 req/min/ip)  
- [ ] Size limit (vd. body ≤ 5–10MB)  
- [ ] Log có trace-id, phân tách api/worker  
- [ ] Báo cáo benchmark ngắn gọn (bảng số liệu + nhận xét)

**KPI tuần 4**
- Error rate API < 1% trong 15 phút stress test nhẹ  
- P95 latency `/ask` ≤ 3s (dev, dữ liệu mẫu)  
- 100% endpoint có test; coverage tổng ≥ 75%

---

## Bài tập/Task mẫu theo ngày (gợi ý)

- **W1D1–D2**: Python async, typing, pydantic; dựng skeleton + healthcheck.  
- **W1D3–D5**: CRUD 3 endpoint, DI, error handler, test & coverage.  
- **W2D1**: Cài RabbitMQ local, tạo exchange/queue.  
- **W2D2–D3**: Publish từ API, consume ở worker (ack/retry/DLQ).  
- **W2D4–D5**: Idempotency + metrics/log; mini load test.  
- **W3D1**: Chọn vector store, viết ingest+embed.  
- **W3D2**: `/ask` + retrieval + prompt template + citations.  
- **W3D3**: Streaming.  
- **W3D4–D5**: Bộ Q&A mẫu, kiểm thử chất lượng.  
- **W4D1**: Rate-limit, size-limit, CORS, secrets.  
- **W4D2**: Logging/tracing cơ bản.  
- **W4D3**: Benchmark nhanh & tối ưu.  
- **W4D4–D5**: Viết tài liệu & demo.

---

## Ma trận KPI đánh giá (điểm & trọng số)

| Nhóm | Chỉ số | Mục tiêu | Trọng số |
|---|---|---:|---:|
| Kiến thức | Quiz Python/FastAPI/RabbitMQ/LangChain ≥ 80% | ≥80% | 10% |
| Chất lượng code | Lint (`ruff`=0), `mypy`≥80%, coverage ≥75% | Đạt | 25% |
| API | P95 `/health` ≤50ms, error rate <1% | Đạt | 10% |
| Worker | P95 xử lý job ≤3s, fail <1%, DLQ=0 (bình thường) | Đạt | 20% |
| Tính năng RAG | Hit-rate bộ Q&A ≥70%, có citations ≥90% | Đạt | 20% |
| Giao hàng | ≥10 PR/tháng, PR nhỏ, review pass ≤2 vòng | Đạt | 15% |

> Cách chấm: đạt mục tiêu = 100% điểm mục KPI; thiếu nhẹ (≤10%) = 70%; thiếu nhiều = 0%. Tổng điểm = Σ(điểm * trọng số).

---

## Tiêu chí “Definition of Done” cho PR
- Có test đi kèm; pass CI; lấp khoảng trống tài liệu nếu ảnh hưởng API/ops.  
- Log & error được xử lý rõ (không `print`).  
- Config qua env, **không** commit secret.  
- Đoạn code mới có typing, docstring ngắn.

---

## Stretch goals (nếu xong sớm)
- Thêm **LangGraph** cho điều phối flow phức tạp.  
- Thêm cache (Redis) cho retrieval.  
- Eval bán tự động (vd. RAGAS) & prompt guard (prompt injection checklist).  
- Container hoá & compose: `api` + `worker` + `rabbitmq` + `vector-db`.
