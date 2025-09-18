# MẪU CHECKLIST HẰNG TUẦN (4 TUẦN)

> Dựa trên kế hoạch 1 tháng: Python + FastAPI + RabbitMQ + LangChain (RAG)

## Hướng dẫn sử dụng
- Mỗi tuần tick các đầu mục dưới đây và **đính kèm bằng chứng** (link PR, log, ảnh benchmark…).
- Cuối tuần có phần **Mini-review** để tổng kết tiến độ và rủi ro.

---

## Tuần 1 – Python & FastAPI nền tảng
**Mục tiêu**
- Nắm `async/await`, typing, pydantic v2; FastAPI router/DI/validation/errors/OpenAPI.
- Thiết lập `ruff`, `mypy`, `pytest` + test API.

**Checklist Deliverables**
- [ ] Repo skeleton theo cấu trúc chuẩn
- [ ] `GET /health`
- [ ] `POST /items`, `GET /items/{id}`, `DELETE /items/{id}`
- [ ] Exception handler + chuẩn JSON error (kèm trace-id)
- [ ] OpenAPI hiển thị đủ schema & mô tả
- [ ] Test unit + API (`pytest`, `httpx`)

**Quality Gates**
- [ ] `ruff` = 0 lỗi (evidence CI): `____`
- [ ] `mypy` ≥ 80% (module mới): `____%`
- [ ] Coverage ≥ 70%: `____%`

**KPI snapshot**
- [ ] 95p latency `/health` ≤ 50ms (local): `____ ms`
- [ ] ≥ 1 PR/Ngày (min 3–5 PR/tuần): `____ PR`

**Mini-review**
- Done tốt: `____`
- Blockers/rủi ro: `____`
- Kế hoạch tuần 2: `____`

---

## Tuần 2 – RabbitMQ & Worker
**Mục tiêu**
- RabbitMQ: exchange/queue/routing/ack/retry/DLQ.
- Worker async (`aio-pika` hoặc Celery) + job “chunk & chuẩn hoá tài liệu”.

**Checklist Deliverables**
- [ ] `POST /ingest` publish message
- [ ] Worker consume → chunk → lưu metadata
- [ ] Idempotency (chạy lại không trùng dữ liệu)
- [ ] Retry có backoff (≤ 3 lần), DLQ cho job lỗi
- [ ] Health/metrics cho worker (log heartbeat or `/metrics`)

**Quality Gates**
- [ ] Cấu hình DLX/DLQ qua env: `____`
- [ ] Log structured (job_id, status, latency): `____`

**KPI snapshot**
- [ ] Throughput ≥ 30 job/phút (file nhỏ, local): `____`
- [ ] P95 publish→done ≤ 3s: `____ s`
- [ ] Fail < 1% | Mất mát = 0 | DLQ bình thường = 0: `____`

**Mini-review**
- Done tốt: `____`
- Blockers/rủi ro: `____`
- Kế hoạch tuần 3: `____`

---

## Tuần 3 – LangChain & RAG tối thiểu
**Mục tiêu**
- Ingest → chunk → embed → upsert vector; `/ask` với retrieval + prompt template + citations + streaming.

**Checklist Deliverables**
- [ ] Chọn & khởi tạo vector store (FAISS/Chroma)
- [ ] Pipeline ingest + embed + upsert/bulk upsert
- [ ] API `/ask` có top-k, prompt template, trích dẫn nguồn
- [ ] Streaming token (SSE/chunked)
- [ ] Bộ Q&A 10–20 câu + script đánh giá

**Quality Gates**
- [ ] Guardrails đơn giản (ngoài miền dữ liệu → từ chối): `____`
- [ ] Log usage/tokens (nếu dùng API tính phí): `____`

**KPI snapshot**
- [ ] Hit-rate ≥ 70%: `____%`
- [ ] ≥ 90% câu có ≥1 citation: `____%`
- [ ] TTFB `/ask` ≤ 1.5s (local): `____ s`

**Mini-review**
- Done tốt: `____`
- Blockers/rủi ro: `____`
- Kế hoạch tuần 4: `____`

---

## Tuần 4 – Tích hợp, chất lượng & Demo
**Mục tiêu**
- Hardening: CORS, rate-limit, body size limit, secrets, request-id, access log.
- Quan sát: logging có trace-id, basic tracing/metrics.
- Benchmark và tài liệu hoá.

**Checklist Deliverables**
- [ ] `README` (run, test, kiến trúc, luồng dữ liệu)
- [ ] `API.md` (endpoint + ví dụ)
- [ ] `OPERATIONS.md` (env, queue, retry/DLQ, backup đơn giản)
- [ ] Benchmark `/ask` & `/ingest` (P50/P95) với `hey/ab`
- [ ] Demo ingest 100 tài liệu nhỏ & hỏi 5 câu thực tế

**Quality Gates**
- [ ] Rate-limit (ví dụ 60 req/min/ip): `____`
- [ ] Body size limit (5–10MB): `____`
- [ ] Log phân tách api/worker + trace-id: `____`

**KPI snapshot**
- [ ] Error rate API < 1% (stress nhẹ 15’) : `____%`
- [ ] P95 `/ask` ≤ 3s (dev, dữ liệu mẫu): `____ s`
- [ ] 100% endpoint có test | coverage tổng ≥ 75%: `____%`

**Mini-review**
- Kết quả benchmark & nhận xét: `____`
- Những phần còn nợ: `____`
- Kế hoạch tháng sau / handover: `____`

---

## Definition of Done (cho mọi PR)
- [ ] Có test kèm; pass CI.
- [ ] Không commit secrets; config qua env.
- [ ] Logging chuẩn (không `print`); error được map rõ ràng.
- [ ] Typing đủ; docstring ngắn; cập nhật tài liệu khi thay đổi API/ops.
