# API Rate Limiting Strategy

> File: `docs/api-rate-limiting-strategy.md`  
> Author: <!-- TODO: Tên người soạn -->  
> Date: <!-- TODO: yyyy-mm-dd -->  
> Version: <!-- TODO: v1.0.0 -->

---

## 1. Mục tiêu tài liệu

**Mục đích:**

- Mô tả **chiến lược rate limiting** cho API của hệ thống.
- Chuẩn hoá cách thiết kế, triển khai và vận hành rate limiting trong Laravel.
- Giảm thiểu rủi ro **spam / abuse / brute-force** mà vẫn đảm bảo trải nghiệm người dùng.

**Kết quả mong đợi:**

- Người đọc tài liệu có thể:
  - Hiểu rõ cách Laravel hỗ trợ rate limiting.
  - Biết **chúng ta áp dụng rate limit như thế nào cho từng loại endpoint**.
  - Triển khai & cấu hình được trong dự án mà **không cần hỏi lại quá nhiều**.

---

## 2. Bối cảnh & phạm vi

### 2.1. Bối cảnh hệ thống

- Framework: **Laravel** v<!-- TODO: version, ví dụ: 11.x / 12.x -->
- Kiến trúc: <!-- TODO: ví dụ: RESTful API + SPA React / Mobile app -->
- Auth:
  - <!-- TODO: Sanctum / Passport / JWT / khác -->
- Cache / Queue:
  - Cache driver: <!-- TODO: redis / database / file / ... -->
  - Queue: <!-- TODO: redis / database / ... -->

### 2.2. Phạm vi áp dụng rate limiting

- Public endpoints:
  - Ví dụ: `/api/login`, `/api/register`, `/api/password/forgot`, ...
- Authenticated endpoints:
  - Ví dụ: `/api/users/*`, `/api/orders/*`, ...
- Internal / service-to-service endpoints (nếu có):
  - Ví dụ: `/api/internal/*`, webhook internal, ...

> **Ngoài phạm vi:**  
> <!-- TODO: Liệt kê những phần chưa áp dụng rate limiting ở giai đoạn này nếu có -->

---

## 3. Yêu cầu & mục tiêu kỹ thuật

### 3.1. Yêu cầu chức năng (Functional)

- Hạn chế số lượng request:
  - Theo **IP** cho các endpoint public có nguy cơ bị đánh brute-force (login, OTP, ...)
  - Theo **user / token** cho các endpoint authenticated.
  - Theo **route / route group** với các endpoint đặc biệt (search, upload, ...).
- Có khả năng **phân loại tier** (nếu dùng):
  - Free / Pro / Enterprise với quota khác nhau.

### 3.2. Yêu cầu phi chức năng (Non-functional)

- Hoạt động tốt trong môi trường **multi-instance / multi-container**.
- Không gây “nghẽn cổ chai” (bottleneck) performance.
- Dễ mở rộng & maintain:
  - Thêm limit mới không phải sửa quá nhiều chỗ.
- Có **logging & monitoring** tối thiểu để phát hiện abuse.

### 3.3. Các ràng buộc / Assumptions

- App sẽ chạy trên:
  - Môi trường: <!-- TODO: local / staging / production / k8s / docker swarm / ... -->
- Hệ thống đã/ chưa có:
  - Reverse proxy (Nginx, Cloudflare, API Gateway, ...): <!-- TODO -->
- Storage dùng cho rate limiting:
  - <!-- TODO: dự kiến dùng driver nào? -->

---

## 4. Tổng quan rate limiting trong Laravel

### 4.1. Khả năng built-in

**Các cơ chế chính:**

- Middleware `throttle:60,1` (kiểu cũ):
  - Ý nghĩa: `60` request / `1` phút / per key.
- **RateLimiter API** (kiểu mới):
  - Định nghĩa trong: `App\Providers\RouteServiceProvider` hoặc `App\Providers\AuthServiceProvider` (tuỳ convention).
  - Ví dụ:

```php
// Ví dụ: placeholder, sẽ được chỉnh sửa cho phù hợp với dự án
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});