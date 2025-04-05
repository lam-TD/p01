
App thống kê thu chi cá nhân

Danh mục:
- Thu
  - Lương
  - Tiền thưởng
  - Tiền bán hàng
- Chi:
  - Tiền ăn uống
  - Tiền nhà
  - Tiền điện
  - Tiền nước
  - Tiền internet
  - Tiền học tập
  - Tiền giải trí

=> Cơ sở dữ liệu:

payment_categories:
- id
- name
- type: income or expense

payment_methods:
- id
- name
- type: cash or bank

payments:
- id
- amount
- payment_category_id
- payment_method_id
- description
- created_at
- updated_at


