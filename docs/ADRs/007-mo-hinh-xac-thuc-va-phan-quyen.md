# ADR 007: Cơ chế Xác thực và Phân quyền

## Context (Bối cảnh)
Hệ thống cần đảm bảo:
  - **Chỉ người dùng có quyền mới có thể truy cập tập tin**.
  - **Hỗ trợ phân quyền theo vai trò (Role-Based Access Control - RBAC)**.
  - **Cho phép cấp quyền linh hoạt theo doanh nghiệp (Multi-Tenant Authorization)**.
  - **Có thể mở rộng để hỗ trợ xác thực OAuth2, SSO nếu cần**.

## Decision (Quyết định)
Chúng ta quyết định sử dụng **Laravel Sanctum** để xác thực API và **Spatie Laravel Permission** để quản lý phân quyền.

## Alternatives (Các phương án thay thế)
1. **Laravel Sanctum + Spatie Laravel Permission [CHỌN]**
  ✅ Sanctum nhẹ, hỗ trợ token-based authentication.  
  ✅ Spatie Laravel Permission hỗ trợ phân quyền theo Role & Permission.  
  ✅ Dễ mở rộng với OAuth2 nếu cần.  
  ⚠ Cần thiết kế mô hình phân quyền chặt chẽ để tránh rò rỉ dữ liệu.  

2. **Laravel Passport (OAuth2)**
  ✅ Chuẩn OAuth2, phù hợp với hệ thống lớn.  
  ⚠ Phức tạp hơn, không cần thiết nếu chỉ xác thực API nội bộ.  

3. **JWT (JSON Web Token)**
  ✅ Token-based authentication, không cần lưu session.  
  ⚠ Quản lý token phức tạp hơn nếu cần revoke quyền.  

## Consequences (Hệ quả)
  ✅ **Sanctum** đơn giản và hiệu quả cho API authentication.  
  ✅ **Spatie Laravel Permission** giúp kiểm soát quyền truy cập tập tin linh hoạt.  
  ⚠ Cần xây dựng hệ thống kiểm soát permission theo doanh nghiệp để tránh xung đột quyền.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Có thể bổ sung **OAuth2 / SSO** nếu cần tích hợp với bên thứ ba.  
  - Xây dựng cơ chế **Audit Logs** để theo dõi ai truy cập file nào.  
