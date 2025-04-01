# ADR 002: Lựa chọn Cơ sở Dữ liệu cho Hệ thống Quản lý Tập tin  

## Context (Bối cảnh)  
Hệ thống cần một cơ sở dữ liệu có khả năng:  
   - **Hỗ trợ Multi-Tenant** với mô hình **Database per Tenant**.  
   - **Hiệu suất cao**, hỗ trợ truy vấn tập tin nhanh.  
   - **Tích hợp tốt với Laravel**, hỗ trợ ORM mạnh mẽ.  
   - **Dễ mở rộng**, có khả năng sharding nếu cần.  

## Decision (Quyết định)  
Chúng ta quyết định sử dụng **PostgreSQL** làm cơ sở dữ liệu chính.  

## Alternatives (Các phương án thay thế)  
1. **PostgreSQL [CHỌN]**  
   ✅ Hỗ trợ tốt **multi-tenancy** với **schemas hoặc database riêng**.  
   ✅ Cung cấp tính năng **JSONB, full-text search** phù hợp cho metadata tập tin.  
   ✅ Hỗ trợ **Replication, Partitioning** để mở rộng khi cần.  
   ⚠ Cần cấu hình tối ưu khi số lượng database lớn.  

2. **MySQL**  
   ✅ Dễ sử dụng, phổ biến.  
   ✅ Hỗ trợ replication tốt.  
   ⚠ Không mạnh về **JSONB và full-text search** như PostgreSQL.  

3. **MongoDB (NoSQL)**  
   ✅ Linh hoạt với dữ liệu không có cấu trúc cố định.  
   ✅ Hỗ trợ sharding tự động.  
   ⚠ Không phù hợp cho mô hình relational với **ACID transactions**.  

## Consequences (Hệ quả)  
   ✅ **PostgreSQL** cung cấp các tính năng mạnh mẽ để quản lý dữ liệu doanh nghiệp.  
   ✅ Dễ mở rộng và tối ưu hóa cho Multi-Tenant.  
   ⚠ Cần có chiến lược quản lý connection pooling để tránh quá tải.  

## Status (Trạng thái)  
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)  
   - Khi số lượng tenants tăng cao, có thể sử dụng **CitusDB** (một extension của PostgreSQL) để sharding dữ liệu.  
   - Có thể tích hợp **Redis** để caching metadata file nhằm tăng tốc truy vấn.  
   - Cần có kế hoạch **sao lưu và phục hồi dữ liệu** cho từng database riêng biệt.