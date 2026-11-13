# MiniPos - Quản lý bán hàng thông minh

Website MiniPos là bài tập nhóm của học phần Thiết kế và Lập trình web. Dự án viết bằng PHP phía máy chủ (từ Bài tập nhóm số 4 và 5); ở Bài tập nhóm số 6, dữ liệu được chuyển sang MySQL/MariaDB và truy cập bằng PDO.

## 1. Yêu cầu môi trường

- PHP 8.1 trở lên.
- Composer.
- MySQL 8 hoặc MariaDB 10.4 trở lên (cần extension `pdo_mysql` của PHP).
- Trình duyệt web hiện đại.
- Git.

## 2. Cài đặt và chạy website

### Cài đặt thư viện

Mở PowerShell tại thư mục gốc của dự án và chạy:

```bash
composer install
```

### Tạo cơ sở dữ liệu

Chạy từ thư mục gốc của dự án (lệnh `schema.sql` xóa và tạo lại CSDL `ltweb_nhom05`, nên chạy lại nhiều lần được):

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

Với XAMPP mặc định (tài khoản `root`, không mật khẩu) bỏ tùy chọn `-p`. Kết nối được cấu hình trong tệp `.env` (không commit). Tạo từ bản mẫu rồi sửa giá trị:

```bash
cp .env.example .env
```

| Biến          | Ý nghĩa                                              |
| ------------- | ---------------------------------------------------- |
| `APP_DEBUG`   | `true` chỉ trên máy cá nhân; thiếu hoặc khác thì tắt |
| `DB_HOST`     | Máy chủ CSDL (bắt buộc)                              |
| `DB_PORT`     | Cổng, mặc định `3306` (tùy chọn)                     |
| `DB_DATABASE` | Tên CSDL (bắt buộc)                                  |
| `DB_USERNAME` | Tài khoản CSDL (bắt buộc)                            |
| `DB_PASSWORD` | Mật khẩu (bắt buộc phải có khai báo, được để rỗng)   |

Thiếu biến bắt buộc, trang trả về lỗi 500 và nguyên nhân được ghi vào log PHP.

Tạo tài khoản CSDL riêng cho ứng dụng

Để hạn chế quyền truy cập CSDL, ứng dụng có thể sử dụng tài khoản riêng nhom05_app thay vì tài khoản quản trị root.

Thực hiện các bước sau:

Đăng nhập phpMyAdmin hoặc MySQL bằng tài khoản quản trị CSDL (mặc định XAMPP là root).

Mở và thực thi tệp database/tai-khoan.sql.

Trước khi thực thi, thay chuỗi CHUOI_GIU_CHO_NAY trong tệp SQL bằng mật khẩu riêng cho tài khoản nhom05_app.

Sau khi tạo tài khoản thành công, cập nhật thông tin kết nối trong .env:

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ltweb_nhom05
DB_USERNAME=nhom05_app
DB_PASSWORD=MAT_KHAU_DA_TAO

Thay MAT_KHAU_DA_TAO bằng mật khẩu đã sử dụng khi tạo tài khoản.

Tài khoản nhom05_app chỉ được cấp bốn quyền SELECT, INSERT, UPDATE, DELETE trên CSDL ltweb_nhom05. Tài khoản này không được sử dụng để quản trị hoặc thay đổi cấu trúc CSDL.

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

## 3. Tài khoản thử nghiệm

Mọi tài khoản đều dùng mật khẩu `matkhau123` (băm bằng `password_hash`, lưu trong bảng `nguoi_dung` do `database/seed.sql` tạo):

| Email              | Vai trò     |
| ------------------ | ----------- |
| `admin@minipos.vn` | `quan_tri`  |
| `nv01@minipos.vn`  | `nhan_vien` |
| `nv02@minipos.vn`  | `nhan_vien` |

Trang đăng nhập: `/dang-nhap.php`. Trang quản trị: `/quan-tri.php`. Các tài khoản này chỉ dùng để kiểm thử, không dùng cho hệ thống thật.

## 4. Bảng chức năng – URL – tệp PHP

| Chức năng                                                            | URL                                                            | Tệp PHP chính                                   | Quyền                               |
| -------------------------------------------------------------------- | -------------------------------------------------------------- | ----------------------------------------------- | ----------------------------------- |
| Trang chủ, sản phẩm đã xem gần đây                                   | `/index.php`                                                   | `index.php`                                     | Công khai                           |
| Danh sách sản phẩm: tìm kiếm, lọc, sắp xếp, phân trang (12 SP/trang) | `/danh-sach.php`                                               | `danh-sach.php`                                 | Công khai                           |
| Chi tiết sản phẩm theo mã                                            | `/chi-tiet.php?id=1`                                           | `chi-tiet.php`                                  | Công khai                           |
| Giỏ hàng: thêm, đổi số lượng, xóa, tính tổng tiền                    | `/gio-hang.php`                                                | `gio-hang.php`                                  | Công khai                           |
| Đặt hàng (một giao dịch, trừ tồn kho)                                | `/dat-hang.php`                                                | `dat-hang.php`                                  | Đã đăng nhập                        |
| Danh sách đơn hàng và chi tiết đơn                                   | `/don-hang.php`, `/chi-tiet-don.php`                           | `don-hang.php`, `chi-tiet-don.php`              | Đã đăng nhập (quản trị xem mọi đơn) |
| Gửi phiếu báo cáo và tải ảnh minh chứng                              | `/lien-he.php`                                                 | `lien-he.php`                                   | Công khai                           |
| Đăng ký, đăng nhập, đăng xuất                                        | `/dang-ky.php`, `/dang-nhap.php`, `/dang-xuat.php`             | `dang-ky.php`, `dang-nhap.php`, `dang-xuat.php` | Công khai                           |
| Quản trị: đơn hàng, đổi trạng thái đơn, phiếu liên hệ                | `/quan-tri.php`                                                | `quan-tri.php`                                  | `quan_tri`                          |
| Thêm, sửa, xóa sản phẩm                                              | `/them-san-pham.php`, `/sua-san-pham.php`, `/xoa-san-pham.php` | tương ứng                                       | `quan_tri`                          |
| Giới thiệu nhóm, trang cá nhân thành viên                            | `/gioi-thieu.php`, `/thanhvien/<mssv>/gioithieu.php`           | `gioi-thieu.php`                                | Công khai                           |
| Trang lỗi 403, 404, 500                                              | `/403.php`, `/404.php`, `/500.php`                             | cùng tên                                        | —                                   |

### API JSON

Các ví dụ yêu cầu nằm trong `api.http`.

| Phương thức, URL                                 | Chức năng                    | Mã trả về          |
| ------------------------------------------------ | ---------------------------- | ------------------ |
| `GET /api/san-pham.php?q=&dm=&giaToiDa=&sx=`     | Tìm sản phẩm (tối đa 20 mục) | 200                |
| `GET /api/san-pham.php?id=1`                     | Chi tiết một sản phẩm        | 200, 400, 404      |
| `GET /api/gio-hang.php`                          | Xem giỏ hàng trong session   | 200                |
| `POST /api/gio-hang.php` (JSON `id`, `so_luong`) | Thêm sản phẩm vào giỏ        | 200, 400, 404, 422 |
| Phương thức khác                                 | Không hỗ trợ                 | 405                |

## 5. Cấu trúc mã nguồn

- `inc/`: cấu hình (`config.php`), kết nối PDO (`db.php`), hàm dùng chung, xác thực và phân quyền (`xac-thuc.php`), header, footer.
- `database/`: `schema.sql` (tạo lại CSDL từ đầu) và `seed.sql` (dữ liệu mẫu, tài khoản thử).
- `src/Models/`: lớp thực thể sản phẩm.
- `src/Data/`: các lớp truy cập dữ liệu nhận PDO (`KhoSanPham`, `KhoDanhMuc`, `KhoDonHang`, `KhoNguoiDung`, `KhoLienHe`); mọi câu SQL nằm ở đây.
- `src/Services/`: lớp quản lý giỏ hàng bằng session.
- `api/`: các điểm cuối trả JSON.
- `uploads/`: ảnh minh chứng được tải lên.
- `logs/php-error.log`: nhật ký lỗi PHP.
- `css/`, `js/`, `images/`: tài nguyên giao diện.
- `kiemtra/`: ảnh và kết quả kiểm thử.
- `thanhvien/`: trang cá nhân của thành viên (Phần B) và các bảng riêng `tv_...` trong `schema.sql`.

## 6. Xử lý dữ liệu và bảo mật

- Mọi truy vấn dùng PDO với prepared statement; chuỗi `ORDER BY` chỉ chọn từ danh sách cho phép.
- Đặt hàng chạy trong một giao dịch: trừ tồn kho bằng `UPDATE ... WHERE ton_kho >= ?`, thiếu hàng thì rollback; đơn giá được lưu tại thời điểm đặt.
- Phân trang bằng `COUNT(*)` và `LIMIT ? OFFSET ?`.
- Mật khẩu lưu bằng `password_hash()`, kiểm tra bằng `password_verify()`; đăng nhập báo một lỗi chung và đổi mã phiên.
- Phân quyền bằng `yeuCauDangNhap()` và `yeuCauQuyen('quan_tri')`; tài khoản tự đăng ký có vai trò mặc định `nhan_vien`, không nhận vai trò từ form.
- Kiểm tra dữ liệu ở phía máy chủ trước khi xử lý.
- Dùng hàm `e()` để escape dữ liệu động trước khi hiển thị HTML.
- Dùng CSRF token cho các thao tác POST.
- Chuyển hướng sau khi xử lý thành công theo mô hình Post/Redirect/Get.
- Kiểm tra kiểu ảnh thật bằng `finfo` và dùng tên tệp ngẫu nhiên.
- Ghi lỗi PHP vào `logs/php-error.log`.
- Không đưa `vendor/` và dữ liệu phát sinh khi chạy vào Git.

## 7. Ghi chú

- Chế độ gỡ lỗi được điều khiển bằng biến `APP_DEBUG` trong tệp `.env`; đặt `true` khi phát triển trên máy cá nhân và `false` khi triển khai thực tế.
- Giỏ hàng hiện hỗ trợ quản lý sản phẩm và tính tổng tiền; thanh toán trực tuyến chưa được triển khai.
- Tìm kiếm tức thì trên trang danh sách gọi API và hiển thị tối đa 20 kết quả; bấm "Áp dụng" để xem đầy đủ và phân trang.
- Trước khi nộp: clone lại repo vào thư mục mới, chạy `composer install`, tạo CSDL từ `schema.sql` và `seed.sql`, rồi mở thử các trang.
- Các kết quả kiểm thử PHP, HTML, Lighthouse và ảnh chụp phải được cập nhật theo kết quả thực tế trước khi nộp.
