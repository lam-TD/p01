# ADR 001: Kiến trúc hệ thống quản lý tập tin cho nhiều doanh nghiệp

## Context (Bối cảnh)
Chúng ta đang xây dựng một hệ thống quản lý tập tin (File Management System) hỗ trợ **nhiều doanh nghiệp**. Hệ thống cần:  
   - Hỗ trợ lưu trữ, tìm kiếm, chia sẻ tập tin giữa người dùng trong cùng một doanh nghiệp.  
   - Đảm bảo bảo mật, giới hạn quyền truy cập theo doanh nghiệp và người dùng.  
   - Có khả năng mở rộng để hỗ trợ nhiều doanh nghiệp mà không ảnh hưởng hiệu suất.  

Câu hỏi quan trọng: **Nên thiết kế hệ thống theo kiến trúc Single-Tenant hay Multi-Tenant?**  

## Decision (Quyết định)
Chúng ta quyết định sử dụng **kiến trúc Multi-Tenant với Database per Tenant** để hỗ trợ nhiều doanh nghiệp.  

## Alternatives (Các phương án thay thế)
1. **Single-Tenant (Mỗi doanh nghiệp có một instance riêng biệt)**  
   ✅ Đơn giản trong quản lý dữ liệu.  
   ✅ Tăng cường bảo mật giữa các doanh nghiệp.  
   ⚠ Tốn tài nguyên server khi mở rộng.  
   ⚠ Cần nhiều effort để vận hành và cập nhật hệ thống.  

2. **Multi-Tenant với Database chung (Shared Database, Tenant ID trong bảng dữ liệu)**  
   ✅ Tiết kiệm tài nguyên, dễ mở rộng.  
   ✅ Dễ dàng cập nhật hệ thống.  
   ⚠ Rủi ro về bảo mật dữ liệu nếu có lỗi trong truy vấn.  
   ⚠ Cần tối ưu hiệu suất để tránh tranh chấp tài nguyên giữa doanh nghiệp.  

3. **Multi-Tenant với Database riêng cho mỗi doanh nghiệp (Database per Tenant) [CHỌN]**  
   ✅ Cân bằng giữa hiệu suất và bảo mật.  
   ✅ Giảm nguy cơ rò rỉ dữ liệu giữa các doanh nghiệp.  
   ✅ Có thể scale bằng cách di chuyển từng tenant sang server khác nếu cần.  
   ⚠ Phức tạp hơn khi quản lý kết nối đến nhiều database.  

## Consequences (Hệ quả)
   ✅ Mỗi doanh nghiệp có database riêng, giảm rủi ro truy cập nhầm dữ liệu.  
   ✅ Dễ di chuyển, sao lưu hoặc tách riêng từng doanh nghiệp khi cần.  
   ⚠ Cần có cơ chế quản lý kết nối database hiệu quả để tránh quá tải.  
   ⚠ Việc triển khai có thể phức tạp hơn do phải tự động tạo và quản lý nhiều database.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
   - Nếu số lượng doanh nghiệp rất lớn, có thể cân nhắc **Database Sharding** để tránh quá tải.  
   - Cần xây dựng cơ chế **Auto-scaling** để đảm bảo hiệu suất khi số lượng doanh nghiệp tăng lên.  
   - Cân nhắc **tích hợp S3 hoặc dịch vụ lưu trữ đám mây** để tối ưu chi phí lưu trữ tập tin.  
   - Cần có kế hoạch **sao lưu và phục hồi dữ liệu** cho từng database riêng biệt.
   - Cần có cơ chế **monitoring và logging** để theo dõi hiệu suất và bảo mật của từng database.
   - Cần có kế hoạch **kiểm tra bảo mật** định kỳ để đảm bảo không có lỗ hổng giữa các doanh nghiệp.
   - Cần có cơ chế **quản lý người dùng và quyền truy cập** để đảm bảo chỉ những người dùng hợp lệ mới có thể truy cập vào dữ liệu của doanh nghiệp mình.
   - Cần có kế hoạch **đào tạo và hỗ trợ** cho các doanh nghiệp trong việc sử dụng hệ thống.
   - Cần có cơ chế **thông báo và cảnh báo** cho các doanh nghiệp khi có sự cố xảy ra với hệ thống.
   - Cần có kế hoạch **bảo trì và nâng cấp hệ thống** định kỳ để đảm bảo hiệu suất và bảo mật.
   - Cần có cơ chế **phân tích và báo cáo** để giúp các doanh nghiệp theo dõi hiệu suất và sử dụng hệ thống.