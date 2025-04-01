# ADR 005: Lựa chọn Cơ chế Upload File

## Context (Bối cảnh)
Người dùng có thể tải lên tập tin dung lượng lớn, cần hỗ trợ:
  - **Tải lên không bị gián đoạn**, ngay cả khi mạng yếu.
  - **Hỗ trợ resume upload** để tiếp tục tải lên khi bị gián đoạn.
  - **Bảo mật file**, chỉ cho phép người dùng có quyền truy cập.

## Decision (Quyết định)
Chúng ta quyết định sử dụng **Multipart Upload** kết hợp với **Pre-signed URL** của S3.

## Alternatives (Các phương án thay thế)
1. **Multipart Upload + Pre-signed URL [CHỌN]**
  ✅ Chia nhỏ file thành nhiều phần để tải lên nhanh hơn.  
  ✅ Hỗ trợ tiếp tục upload nếu kết nối bị gián đoạn.  
  ✅ Pre-signed URL giúp tải lên file trực tiếp mà không qua backend.  
  ⚠ Cần có cơ chế kiểm soát thời gian hết hạn của URL.  

2. **Upload trực tiếp qua Backend API**
  ✅ Đơn giản, dễ kiểm soát quyền truy cập.  
  ⚠ Gây tải lớn cho server backend, không tối ưu.  

3. **WebSocket Streaming Upload**
  ✅ Tải lên real-time, thích hợp cho file nhỏ.  
  ⚠ Không phù hợp với file lớn.  

## Consequences (Hệ quả)
  ✅ Tối ưu hiệu suất tải lên file lớn.  
  ✅ Giảm tải cho backend bằng cách tải file trực tiếp lên S3.  
  ⚠ Cần có chiến lược bảo mật **Pre-signed URL** để tránh lộ file.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Nếu cần lưu trữ nội bộ, có thể thay thế S3 bằng MinIO.  
  - Xây dựng **cơ chế virus scan** để kiểm tra file tải lên.  
