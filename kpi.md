# BẢNG KPI CHẤM ĐIỂM (1 THÁNG)
> Dành cho dev mới tham gia dự án AI: **Python 3.x, FastAPI, LangChain, RabbitMQ**

**Thông tin chung**
- Tháng/Sprint: `____`
- Thành viên: `____`
- Reviewer/Leader: `____`
- Repo/Branch: `____`
- Ngày đánh giá: `____`

## 1) Quy tắc chấm điểm
- Mỗi KPI có **trọng số**. Điểm của KPI = `Đánh giá (100/70/0) × Trọng số / 100`.
- Quy đổi **Đánh giá**:
  - **100**: Đạt đủ mục tiêu (hoặc vượt).
  - **70**: Thiếu nhẹ (≤ 10% so với mục tiêu, hoặc còn 1-2 lỗi nhỏ).
  - **0**: Thiếu nhiều (> 10%), hoặc không đạt yêu cầu trọng yếu.
- **Tổng điểm** = Tổng các điểm KPI.  
- Gợi ý xếp loại: ≥ 85 xuất sắc | 75–84 tốt | 60–74 đạt | < 60 cần cải thiện.

## 2) Bảng KPI tổng hợp
| Nhóm KPI | Chỉ số đo | Mục tiêu | Trọng số | Kết quả đo | Đánh giá (100/70/0) | Điểm quy đổi | Ghi chú / Evidence |
|---|---|---|---:|---|---:|---:|---|
| Kiến thức | Kết quả quiz Python/FastAPI/RabbitMQ/LangChain | ≥ 80% | 10 | `____%` | `____` | `____` | Link quiz: `____` |
| Chất lượng code | Lint (ruff=0), mypy ≥ 80%, coverage ≥ 75% | Đạt | 25 | ruff=`____`; mypy=`____%`; cov=`____%` | `____` | `____` | CI run: `____` |
| API | P95 `/health` ≤ 50ms, error rate < 1% | Đạt | 10 | P95=`____ms`; err=`____%` | `____` | `____` | Report: `____` |
| Worker | P95 xử lý job ≤ 3s, fail < 1%, DLQ=0 (bthg) | Đạt | 20 | P95=`____s`; fail=`____%`; DLQ=`____` | `____` | `____` | Dashboard/log: `____` |
| Tính năng RAG | Hit-rate bộ Q&A ≥ 70%, ≥ 90% câu có citation | Đạt | 20 | hit=`____%`; citation=`____%` | `____` | `____` | Eval sheet: `____` |
| Giao hàng | ≥ 10 PR/tháng; PR nhỏ; review ≤ 2 vòng | Đạt | 15 | PR=`____`; avg vòng=`____` | `____` | `____` | Link board/PRs: `____` |
| **Tổng điểm** |  |  |  |  |  | **____ / 100** |  |

## 3) KPI – tiêu chí chi tiết & cách đo
### 3.1 Kiến thức (10%)
- Bài **quiz** ngắn (20–30 câu): Python async/typing/pydantic, FastAPI DI/validation, RabbitMQ ack/retry/DLQ, LangChain pipeline.  
**Evidence**: điểm quiz & file câu trả lời.

### 3.2 Chất lượng code (25%)
- **ruff**: không còn lỗi (0 error).
- **mypy**: type coverage ≥ 80% module mới.
- **coverage**: ≥ 75% cho code được thêm/sửa.
**Evidence**: CI pipeline, badge, báo cáo HTML/summary.

### 3.3 API (10%)
- Benchmark nhẹ `/health`, `/items/*`: P95 ≤ 50ms (local dev), error < 1% trong 15 phút.  
**Evidence**: kết quả `hey/ab`, log access.

### 3.4 Worker (20%)
- Throughput file nhỏ ≥ 30 job/phút (local).
- Thời gian publish → done **P95 ≤ 3s**.
- Retry/backoff hoạt động; **DLQ = 0** ở luồng bình thường.  
**Evidence**: log structured, biểu đồ/txt report.

### 3.5 Tính năng RAG (20%)
- Bộ Q&A 10–20 câu: **hit-rate ≥ 70%** (đúng tài liệu).
- ≥ **90%** câu trả lời có **citation** nguồn.
- Hỗ trợ **streaming** token.  
**Evidence**: file kết quả đánh giá + link demo.

### 3.6 Giao hàng (15%)
- Tổng **PR ≥ 10** trong tháng; mỗi PR < ~300 LOC (khuyến nghị).
- **Review ≤ 2 vòng** trung bình, lead time hợp lý.  
**Evidence**: thống kê từ Git hosting, board.

## 4) Nhận xét & kế hoạch cải thiện
- Điểm mạnh: `____`
- Cần cải thiện: `____`
- Mục tiêu tháng sau: `____`
