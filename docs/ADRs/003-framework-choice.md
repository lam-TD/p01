# ADR 003: Lựa chọn Framework Phát triển

## Context (Bối cảnh)
Hệ thống cần đảm bảo:
   - **Hiệu suất cao**, có thể mở rộng khi số lượng file tăng lên.
   - **Kiến trúc API-first**, cho phép frontend và backend phát triển độc lập.
   - **Tính linh hoạt**, dễ bảo trì và mở rộng về sau.
   - **Dễ dàng tích hợp với hệ thống xác thực và phân quyền**.

## Decision (Quyết định)
Chúng ta quyết định sử dụng:
   - **Laravel Restful API** cho backend.
   - **React TypeScript** cho frontend.

## Alternatives (Các phương án thay thế)
1. **Laravel Restful API + React TypeScript [CHỌN]**
   ✅ Laravel có hệ sinh thái mạnh, hỗ trợ API-first tốt.  
   ✅ React TypeScript giúp code frontend dễ bảo trì và ít lỗi.  
   ✅ Dễ dàng tích hợp với hệ thống xác thực (Sanctum, OAuth2).  
   ⚠ Cần thiết kế API chuẩn RESTful ngay từ đầu.  

2. **Laravel + Inertia.js**
   ✅ Hỗ trợ SSR tốt, giúp giảm tải cho frontend.  
   ⚠ Không phù hợp với kiến trúc API-first vì frontend phụ thuộc vào backend.  

3. **NestJS + React**
   ✅ NestJS có kiến trúc module hóa tốt hơn cho dự án lớn.  
   ⚠ Cần thêm thời gian học tập và triển khai so với Laravel.  

## Consequences (Hệ quả)
   ✅ **Laravel API-first** giúp backend hoạt động độc lập, dễ mở rộng.  
   ✅ **React TypeScript** đảm bảo frontend mạnh mẽ, dễ bảo trì.  
   ⚠ Cần chuẩn hóa API ngay từ đầu để tránh refactor sau này.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
   - Có thể bổ sung **GraphQL** nếu cần API linh hoạt hơn REST.  
   - Xây dựng **API Documentation (Swagger)** để chuẩn hóa giao tiếp giữa frontend và backend.  
   - Cần có kế hoạch **monitoring và logging** để theo dõi hiệu suất và bảo mật của hệ thống.
   - Cần có kế hoạch **triển khai CI/CD** để tự động hóa quy trình phát triển và triển khai.