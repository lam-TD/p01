# ADR 008: Hệ thống Quản lý Phiên bản Tập tin

## Context (Bối cảnh)
Hệ thống cần hỗ trợ:
  - **Lưu lại lịch sử thay đổi của tập tin**, cho phép khôi phục các phiên bản trước.
  - **Kiểm soát xung đột khi nhiều người dùng chỉnh sửa cùng một file**.
  - **Hỗ trợ kiểm tra ai đã sửa đổi file (Audit Log)**.
  - **Cung cấp cơ chế rollback về phiên bản trước nếu cần**.

## Decision (Quyết định)
Chúng ta quyết định triển khai **cơ chế quản lý phiên bản theo mô hình "Immutable Storage"**, nghĩa là:
  - **Mỗi khi file được cập nhật, tạo một bản sao mới thay vì ghi đè**.
  - **Lưu metadata phiên bản trong database để theo dõi lịch sử**.
  - **Chỉ giữ lại số phiên bản nhất định để tiết kiệm dung lượng (ví dụ: 5 phiên bản gần nhất)**.

## Alternatives (Các phương án thay thế)
1. **Immutable Storage + Metadata Tracking [CHỌN]**
  ✅ Dữ liệu không bị mất do ghi đè.  
  ✅ Hỗ trợ rollback về phiên bản cũ nếu cần.  
  ✅ Kiểm soát tốt lịch sử chỉnh sửa file.  
  ⚠ Tốn dung lượng hơn do lưu nhiều phiên bản.  

2. **Ghi đè file gốc và lưu metadata thay đổi**
  ✅ Tiết kiệm dung lượng hơn.  
  ⚠ Không thể rollback về phiên bản trước.  
  ⚠ Rủi ro mất dữ liệu nếu có lỗi ghi đè.  

3. **Lưu file trên Git (Git-based Versioning)**
  ✅ Có cơ chế diff file tốt.  
  ⚠ Không phù hợp với file nhị phân lớn.  

## Consequences (Hệ quả)
  ✅ Hỗ trợ quản lý phiên bản tập tin mà không ảnh hưởng đến hiệu suất.  
  ✅ Người dùng có thể khôi phục tập tin về phiên bản trước nếu cần.  
  ⚠ Cần có chính sách dọn dẹp (cleanup) để tránh lưu quá nhiều phiên bản.  

## Implementation Plan (Kế hoạch triển khai)
1. Khi người dùng tải lên tập tin mới:
  - Lưu tập tin vào **MinIO (hoặc hệ thống lưu trữ đã chọn)** với tên chứa UUID để tránh trùng lặp.
  - Cập nhật thông tin phiên bản trong **PostgreSQL (hoặc MongoDB nếu cần NoSQL)**.
  - Ghi log người thực hiện thay đổi.

2. Khi người dùng cần khôi phục phiên bản cũ:
  - Truy vấn metadata trong database.
  - Cung cấp URL để tải phiên bản cũ về.

3. Tự động xóa phiên bản cũ (Lifecycle Management):
  - Giữ lại **5 phiên bản gần nhất**.
  - Xóa các phiên bản cũ hơn để tiết kiệm dung lượng.

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Có thể bổ sung **diff tool** để so sánh sự khác biệt giữa các phiên bản.  
  - Tích hợp **Soft Delete + Retention Policy** để tránh mất dữ liệu quan trọng.  
