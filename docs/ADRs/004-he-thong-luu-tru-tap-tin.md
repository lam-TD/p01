# ADR 007: Lựa chọn Hệ thống Lưu trữ Tập tin

## Context (Bối cảnh)
Hệ thống cần hỗ trợ:
  - **Lưu trữ và quản lý tập tin nội bộ**.
  - **Chịu tải cao**, có thể phục vụ nhiều lượt upload/download đồng thời.
  - **Hỗ trợ scaling**, dễ dàng mở rộng khi nhu cầu tăng.
  - **Bảo mật dữ liệu**, đảm bảo file chỉ được truy cập bởi người có quyền.

## Các hệ thống lưu trữ tập tin hiện có
1. **MinIO**: 
  - Là một giải pháp lưu trữ đối tượng mã nguồn mở, tương thích với S3 API.
  - Hỗ trợ **scaling ngang**, dễ dàng mở rộng.
  - Có thể triển khai trên nhiều nền tảng khác nhau (on-premise, cloud).
  - Hỗ trợ **multi-tenancy** tốt, dễ dàng quản lý người dùng và quyền truy cập.
  - Có thể tích hợp với **Nginx** để caching và load balancing.
2. **Ceph**:
  - Là một hệ thống lưu trữ phân tán mạnh mẽ, hỗ trợ cả block storage và object storage.
  - Thích hợp cho các hệ thống lớn, có khả năng mở rộng tốt.
  - Cấu hình phức tạp hơn MinIO, cần nhiều tài nguyên.
3. **Lưu trữ trên Disk Server**:
  - Đơn giản, dễ triển khai.
  - Không chịu tải tốt khi số lượng file lớn.
  - Khó mở rộng khi cần scale.
4. **Amazon S3**:
  - Là dịch vụ lưu trữ đối tượng của Amazon, rất phổ biến và mạnh mẽ.
  - Tuy nhiên, chi phí có thể cao nếu sử dụng nhiều.
  - Không phù hợp với yêu cầu lưu trữ nội bộ.
5. **Google Cloud Storage**:
  - Tương tự như Amazon S3, là dịch vụ lưu trữ đối tượng của Google.
  - Chi phí cũng có thể cao nếu sử dụng nhiều.
  - Không phù hợp với yêu cầu lưu trữ nội bộ.

## Decision (Quyết định)
Chúng ta quyết định sử dụng **MinIO** làm giải pháp lưu trữ tập tin nội bộ, kết hợp với **Nginx để caching và load balancing**.

## Alternatives (Các phương án thay thế)
1. **MinIO + Nginx [CHỌN]**
  ✅ MinIO tương thích với S3 API, dễ dàng tích hợp với Laravel.  
  ✅ Hỗ trợ **scaling ngang**, có thể mở rộng theo nhu cầu.  
  ✅ Kết hợp **Nginx caching** để tối ưu tốc độ tải file.  
  ⚠ Cần có chiến lược backup dữ liệu hợp lý.  

2. **Ceph (Object Storage)**
  ✅ Mạnh mẽ hơn MinIO, phù hợp với hệ thống lớn.
  ✅ Hỗ trợ phân tán tốt.
  ⚠ Cấu hình phức tạp, cần nhiều tài nguyên.

3. **Lưu trữ trên Disk Server**
  ✅ Đơn giản, dễ triển khai.
  ⚠ Không chịu tải tốt khi số lượng file lớn.
  ⚠ Khó mở rộng khi cần scale.

## Consequences (Hệ quả)
  ✅ **MinIO** giúp lưu trữ tập tin an toàn và dễ quản lý.  
  ✅ Có thể tích hợp với CDN (Cloudflare hoặc Nginx) để tăng tốc download.  
  ⚠ Cần có kế hoạch **monitoring và backup** để đảm bảo dữ liệu không bị mất.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Nếu hệ thống lớn hơn, có thể chuyển sang **Ceph** để tăng khả năng phân tán.  
  - Cần tích hợp **cơ chế snapshot và backup định kỳ** để tránh mất dữ liệu.


Người dùng --> Nginx (Caching + Load Balancer) --> MinIO Cluster (Lưu trữ tập tin) --> PostgreSQL (Lưu metadata file)
