# MiniPos - Quản lý bán hàng thông minh

Website MiniPos là bài tập nhóm số 5, học phần Thiết kế và Lập trình web. Dự án được phát triển bằng PHP phía máy chủ từ website của Bài tập nhóm số 4.

## 1. Yêu cầu môi trường

* PHP 8.1 trở lên.
* Composer.
* Trình duyệt web hiện đại.
* Git.

## 2. Cài đặt và chạy website

### Cài đặt thư viện

Mở PowerShell tại thư mục gốc của dự án và chạy:

```bash
composer install
```

### Chạy bằng PHP built-in server

```bash
php -S localhost:8000
```

Sau khi máy chủ khởi động, mở trình duyệt tại:

http://localhost:8000/

Dừng máy chủ bằng `Ctrl + C` trong cửa sổ PowerShell đang chạy PHP.

### Chạy bằng XAMPP

Đặt thư mục dự án trong `htdocs`, khởi động Apache và truy cập:

http://localhost/ltweb-doan-nhom05/

## 3. Tài khoản quản trị thử nghiệm

* **Tên đăng nhập:** `admin`
* **Mật khẩu thử:** `MiniPos@2026`
* **Trang đăng nhập:** `/dang-nhap.php`
* **Trang quản trị:** `/quan-tri.php`

Tài khoản này chỉ dùng để kiểm thử website của nhóm, không dùng cho hệ thống thật. Mật khẩu được lưu dưới dạng hash trong `inc/tai-khoan.php`.

## 4. Bảng chức năng – URL – tệp PHP

| Chức năng                                           | URL                      | Tệp PHP chính    |
| --------------------------------------------------- | ------------------------ | ---------------- |
| Trang chủ, sản phẩm đã xem gần đây                  | `/index.php`             | `index.php`      |
| Danh sách, tìm kiếm, lọc và sắp xếp sản phẩm        | `/danh-sach.php`         | `danh-sach.php`  |
| Chi tiết sản phẩm theo mã                           | `/chi-tiet.php?id=SP001` | `chi-tiet.php`   |
| Giỏ hàng: thêm, đổi số lượng, xóa và tính tổng tiền | `/gio-hang.php`          | `gio-hang.php`   |
| Gửi phiếu báo cáo và tải ảnh minh chứng             | `/lien-he.php`           | `lien-he.php`    |
| Đăng nhập                                           | `/dang-nhap.php`         | `dang-nhap.php`  |
| Đăng xuất                                           | `/dang-xuat.php`         | `dang-xuat.php`  |
| Quản trị và xem phiếu báo cáo                       | `/quan-tri.php`          | `quan-tri.php`   |
| Giới thiệu nhóm                                     | `/gioi-thieu.php`        | `gioi-thieu.php` |
| Trang lỗi không tìm thấy                            | `/404.php`               | `404.php`        |
| Trang lỗi máy chủ                                   | `/500.php`               | `500.php`        |

## 5. Cấu trúc mã nguồn

* `inc/`: cấu hình, hàm dùng chung, header, footer, bảo vệ trang và tài khoản thử nghiệm.
* `src/Models/`: lớp thực thể sản phẩm.
* `src/Data/`: lớp truy cập dữ liệu sản phẩm và phiếu liên hệ.
* `src/Services/`: lớp quản lý giỏ hàng bằng session.
* `data/san-pham.json`: dữ liệu sản phẩm.
* `storage/lien-he.jsonl`: phiếu liên hệ, mỗi phiếu là một dòng JSON.
* `uploads/`: ảnh minh chứng được tải lên.
* `logs/php-error.log`: nhật ký lỗi PHP.
* `css/`, `js/`, `images/`: tài nguyên giao diện.
* `kiemtra/`: ảnh và kết quả kiểm thử.
* `thanhvien/`: trang cá nhân của thành viên.

## 6. Xử lý dữ liệu và bảo mật

* Kiểm tra dữ liệu ở phía máy chủ trước khi xử lý.
* Sử dụng hàm `e()` để escape dữ liệu động trước khi hiển thị HTML.
* Sử dụng CSRF token cho các thao tác POST có liên quan.
* Chuyển hướng sau khi xử lý thành công theo mô hình Post/Redirect/Get.
* Kiểm tra kiểu ảnh thật bằng `finfo` và dùng tên tệp ngẫu nhiên.
* Dùng `password_verify()` để xác thực mật khẩu.
* Sử dụng session cho trạng thái đăng nhập và giỏ hàng.
* Ghi lỗi PHP vào `logs/php-error.log`.
* Không đưa `vendor/` và dữ liệu phát sinh khi chạy vào Git.

## 7. Ghi chú

* Môi trường nộp bài sử dụng `MOI_TRUONG = 'dev'` theo yêu cầu của đề.
* Giỏ hàng hiện hỗ trợ quản lý sản phẩm và tính tổng tiền; thanh toán trực tuyến chưa được triển khai.
* Các kết quả kiểm thử PHP, HTML, Lighthouse và ảnh chụp phải được cập nhật theo kết quả thực tế trước khi nộp.
* Nhóm cần hoàn thiện báo cáo, bảng phân công, lịch sử commit và kiểm tra các trang cá nhân trong `thanhvien/` theo yêu cầu của giảng viên.
