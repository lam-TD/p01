# ADR 006: Lựa chọn Cơ chế Download File

## Context (Bối cảnh)
Hệ thống cần hỗ trợ:
  - **Tải xuống file nhanh chóng**, giảm tải cho backend.
  - **Bảo vệ file**, chỉ người có quyền mới được tải file.

## Decision (Quyết định)
Chúng ta quyết định sử dụng **Pre-signed URL + CDN** để phục vụ file.

## Alternatives (Các phương án thay thế)
1. **Pre-signed URL + CDN [CHỌN]**
  ✅ Giảm tải cho backend, cho phép tải file trực tiếp từ S3/CDN.  
  ✅ URL chỉ có hiệu lực trong thời gian ngắn, tăng bảo mật.  
  ⚠ Cần có cơ chế kiểm soát quyền truy cập chặt chẽ.  

2. **Proxy download qua Backend API**
  ✅ Dễ kiểm soát quyền truy cập.  
  ⚠ Backend phải xử lý từng request, gây tải lớn khi có nhiều file.  

3. **WebSocket File Streaming**
  ✅ Hữu ích khi cần streaming video/audio.  
  ⚠ Không phù hợp với file tĩnh.  

## Consequences (Hệ quả)
  ✅ Tăng tốc tải xuống bằng CDN.  
  ✅ Giảm tải cho backend bằng cách sử dụng S3 trực tiếp.  
  ⚠ Cần có chiến lược **cache file trên CDN** để tối ưu chi phí.  

## Status (Trạng thái)
Accepted ✅  

## Future Considerations (Xem xét trong tương lai)
  - Có thể hỗ trợ **chunked download** nếu cần tối ưu cho file lớn.  
  - Xây dựng cơ chế **watermark file** để chống rò rỉ dữ liệu.  
