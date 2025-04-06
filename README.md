
App thống kê thu chi cá nhân


Các chức năng chính
- Loại thu chi:
  - Mặc định sẽ có 2 loại thu chi: Thu và Chi
  - Người dùng có thể tự tạo loại thu chi
  - Khi tạo loại thu chi, người dùng có thể chọn màu sắc và icon cho loại thu chi
- Quản lý danh mục thu chi:
  - Thêm
  - Sửa
  - Xóa
  - Người dùng có thể tự tạo danh mục thu chi
  - Tất cả danh mục thu chi đều có màu sắc, icon riêng và thuộc về 1 loại thu chi
  - Khi tạo danh mục thu chi, người dùng có thể chọn màu sắc và icon cho danh mục thu chi
- Quản lý phương thức thanh toán:
  - Thêm
  - Sửa
  - Xóa
  - Người dùng có thể tự tạo phương thức thanh toán
  - Tất cả phương thức thanh toán đều có màu sắc, icon riêng và thuộc về 1 loại thu chi
  - Khi tạo phương thức thanh toán, người dùng có thể chọn màu sắc và icon cho phương thức thanh toán
- Quản lý thu chi:
  - Thêm
  - Sửa
  - Xóa
  - Người dùng có thể tự tạo thu chi
  - Có thể cài đặt ngưỡng cảnh báo, nếu vượt quá sẽ đổi màu text thành đỏ
- Thống kê thu chi:
  - Thống kê theo danh mục
  - Thống kê theo phương thức thanh toán
  - Thống kê theo ngày
  - Thống kê theo tuần
  - Thống kê theo tháng
  - Thống kê theo năm

Cơ sở dữ liệu:

- payment_types:
  - id
  - name(unique)
  - color
  - icon
  - created_at
  - updated_at
  - is_active

- payment_categories:
  - id
  - name(unique)
  - description
  - payment_type_id
  - created_at
  - updated_at
  - is_active
  - color
  - icon

- payment_methods:
  - id
  - name(unique)
  - description
  - created_at
  - updated_at
  - is_active
  - color
  - icon

- payments:
  - id
  - amount
  - payment_category_id
  - payment_method_id
  - description
  - user_id
  - created_at
  - updated_at

Laravel API Endpoints:

Payment Type:
- GET /api/payment-types
- POST /api/payment-types
- PUT /api/payment-types/{id}
- DELETE /api/payment-types/{id}
- GET /api/payment-types/{id}

Payment Category:
- GET /api/payment-categories
- POST /api/payment-categories
- PUT /api/payment-categories/{id}
- DELETE /api/payment-categories/{id}
- GET /api/payment-categories/{id}

Payment Method:
- GET /api/payment-methods
- POST /api/payment-methods
- PUT /api/payment-methods/{id}
- DELETE /api/payment-methods/{id}
- GET /api/payment-methods/{id}

Payment:
- GET /api/payments
- POST /api/payments
- PUT /api/payments/{id}
- DELETE /api/payments/{id}
- GET /api/payments/{id}
- GET /api/payments/statistics
- GET /api/payments/statistics/categories
- GET /api/payments/statistics/methods
- GET /api/payments/statistics/days
- GET /api/payments/statistics/weeks
- GET /api/payments/statistics/months
- GET /api/payments/statistics/years


api/payments có thể filter theo:
- payment_type_id
- payment_category_id
- payment_method_id
- user_id
- amount
- description





