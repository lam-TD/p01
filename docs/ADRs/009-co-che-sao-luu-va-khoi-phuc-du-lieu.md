# ADR 009: Cơ chế Sao lưu và Khôi phục Dữ liệu

## Context (Bối cảnh)
Hệ thống cần đảm bảo:
  - **Dữ liệu tập tin và metadata không bị mất trong trường hợp lỗi hệ thống hoặc sự cố phần cứng**.
  - **Khả năng khôi phục nhanh chóng mà không ảnh hưởng đến hiệu suất**.
  - **Cung cấp các chính sách backup phù hợp với dung lượng lưu trữ**.

## Decision (Quyết định)
Chúng ta quyết định triển khai **cơ chế sao lưu theo mô hình Incremental Backup**, kết hợp giữa:
  1. **Sao lưu tập tin trên MinIO theo snapshot hàng ngày**.
  2. **Sao lưu metadata (PostgreSQL) theo chiến lược Point-In-Time Recovery (PITR)**.
  3. **Lưu trữ bản sao dự phòng (Offsite Backup) trên một server riêng biệt**.

## Alternatives (Các phương án thay thế)
1. **Incremental Backup + PITR [CHỌN]**
  ✅ Tiết kiệm dung lượng vì chỉ sao lưu dữ liệu thay đổi.  
  ✅ Khôi phục nhanh chóng mà không ảnh hưởng đến hệ thống.  
  ✅ Hỗ trợ sao lưu offsite để bảo vệ dữ liệu.  
  ⚠ Cần giám sát chặt chẽ để tránh backup bị lỗi.  

2. **Full Backup mỗi ngày**
  ✅ Đơn giản, dễ triển khai.  
  ⚠ Tốn nhiều dung lượng.  
  ⚠ Quá trình backup lâu, ảnh hưởng hiệu suất hệ thống.  

3. **Replication Real-time (Master-Slave)**
  ✅ Phù hợp với hệ thống lớn, giảm downtime.  
  ⚠ Không phải backup thực sự, nếu có lỗi ghi dữ liệu thì replica cũng bị lỗi.  

## Consequences (Hệ quả)
  ✅ **Giảm rủi ro mất dữ liệu**, có thể khôi phục khi cần thiết.  
  ✅ **Không ảnh hưởng nhiều đến hiệu suất**, vì chỉ backup dữ liệu thay đổi.  
  ⚠ Cần theo dõi logs backup để đảm bảo không bị gián đoạn.  

## Implementation Plan (Kế hoạch triển khai)
1. **Sao lưu dữ liệu tập tin (MinIO Snapshot)**
  - Chạy snapshot hàng ngày vào lúc **2:00 AM**.
  - Lưu trữ bản snapshot **trong 7 ngày** trước khi xóa bản cũ.
  - Chuyển một bản snapshot đến **Offsite Backup Server**.

2. **Sao lưu metadata (PostgreSQL)**
  - Bật tính năng **Point-In-Time Recovery (PITR)**.
  - Lưu trữ **bản backup hàng giờ** trong **7 ngày**.

3. **Cơ chế khôi phục**
  - Khi mất dữ liệu, kiểm tra **bản snapshot mới nhất**.
  - Nếu metadata bị lỗi, khôi phục từ PostgreSQL PITR.
  - Nếu cả hệ thống bị mất, dùng Offsite Backup để khôi phục toàn bộ.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Có thể bổ sung **geo-redundant backup** để sao lưu dữ liệu tại nhiều địa điểm.  
  - Cần **cơ chế cảnh báo nếu backup thất bại** để kịp thời xử lý.  
