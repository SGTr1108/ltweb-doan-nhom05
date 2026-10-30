-- database/seed.sql — dữ liệu mẫu (chạy sau schema.sql)
-- Chạy:  mysql -u root ltweb_nhom05 < database/seed.sql
-- Mật khẩu của mọi tài khoản thử: matkhau123 — chuỗi băm tạo bằng
--   php -r "echo password_hash('matkhau123', PASSWORD_DEFAULT);"

SET NAMES utf8mb4;
USE ltweb_nhom05;

-- Danh mục: 4 danh mục có hàng, "Văn phòng phẩm" (id 5) chưa có sản phẩm
INSERT INTO danh_muc (id, ten, slug) VALUES
(1, 'Đồ ăn',          'do-an'),
(2, 'Đồ uống',        'do-uong'),
(3, 'Bánh kẹo',       'banh-keo'),
(4, 'Đồ dùng',        'do-dung'),
(5, 'Văn phòng phẩm', 'van-phong-pham');

-- Sản phẩm: 35 dòng. id 1–12 là 12 sản phẩm của file data/san-pham.json
-- (SP001 → 1 … SP012 → 12). Id 22 hết hàng; id 25 có dấu nháy đơn (Lay's);
-- id 17 có ký tự % để thử LIKE.
INSERT INTO san_pham (id, danh_muc_id, ten, gia, ton_kho, anh, mo_ta) VALUES
(1,  1, 'Bánh snack',                      15000,  50, 'images/lays.png',          'Bánh snack giòn vị tự nhiên.'),
(2,  2, 'Nước suối',                       10000, 100, 'images/AQUAFINA.webp',     'Nước tinh khiết chai 500 ml.'),
(3,  1, 'Mì ly',                           20000,  30, 'images/myly.jpg',          'Mì ly ăn liền, chỉ cần thêm nước sôi.'),
(4,  2, 'Trà sữa',                         25000,  20, 'images/trasua.jpg',        'Trà sữa trân châu pha sẵn.'),
(5,  1, 'Bánh kẹo',                        12000,  60, 'images/chug.jpg',          'Bánh kẹo các loại đóng gói nhỏ.'),
(6,  2, 'Cà phê',                          25000,  40, 'images/cafe.jpg',          'Cà phê pha phin đậm vị.'),
(7,  2, 'Trà xanh',                        12000,  45, 'images/traxanh.jpeg',      'Trà xanh không độ chai 455 ml.'),
(8,  1, 'Bánh quy',                        18000,  35, 'images/banhquy.jpg',       'Bánh quy bơ giòn thơm.'),
(9,  2, 'Nước cam',                        15000,  55, 'images/nuoccam.jpg',       'Nước cam ép đóng chai.'),
(10, 1, 'Cá viên chiên',                   35000,  25, 'images/cvc.webp',          'Cá viên chiên nóng, kèm tương ớt.'),
(11, 1, 'Bánh tráng',                      10000,  70, 'images/banhtrang.jpeg',    'Bánh tráng trộn cay.'),
(12, 1, 'Khoai tây chiên',                 22000,  45, 'images/khoaitaychien.webp','Khoai tây chiên giòn.'),
(13, 1, 'Xúc xích tiệt trùng',             12000,  60, 'images/cvc.webp',          'Xúc xích ăn liền, đóng gói tiện lợi.'),
(14, 1, 'Bánh mì kẹp chả',                 20000,  15, 'images/banhtrang.jpeg',    'Bánh mì kẹp chả lụa, làm trong ngày.'),
(15, 1, 'Cơm cuộn rong biển',              18000,  20, 'images/khoaitaychien.webp','Cơm cuộn rong biển nhân trứng.'),
(16, 1, 'Mì gói Hảo Hảo',                   5000, 150, 'images/myly.jpg',          'Mì gói vị tôm chua cay.'),
(17, 1, 'Combo mì ly khuyến mãi 50%',      30000,  12, 'images/myly.jpg',          'Combo 3 mì ly giảm 50% cho ly thứ ba.'),
(18, 1, 'Trứng gà luộc',                    6000,  40, 'images/cvc.webp',          'Trứng gà luộc sẵn, ăn liền.'),
(19, 2, 'Sữa tươi tiệt trùng 180 ml',       8000,  80, 'images/nuoccam.jpg',       'Sữa tươi tiệt trùng có đường.'),
(20, 2, 'Nước ngọt có ga lon 330 ml',      12000,  90, 'images/AQUAFINA.webp',     'Nước ngọt có ga lon 330 ml.'),
(21, 2, 'Trà đào cam sả',                  28000,  18, 'images/trasua.jpg',        'Trà đào cam sả pha tại quầy.'),
(22, 2, 'Cà phê sữa lon',                  15000,   0, 'images/cafe.jpg',          'Cà phê sữa lon uống liền (tạm hết hàng).'),
(23, 2, 'Nước tăng lực',                   14000,  50, 'images/traxanh.jpeg',      'Nước tăng lực chai 330 ml.'),
(24, 2, 'Nước dừa đóng hộp',               18000,  30, 'images/nuoccam.jpg',       'Nước dừa tươi đóng hộp 330 ml.'),
(25, 3, 'Snack Lay''s vị tự nhiên',        15000,  40, 'images/lays.png',          'Snack khoai tây Lay''s vị tự nhiên.'),
(26, 3, 'Kẹo dẻo trái cây',                 9000,  70, 'images/chug.jpg',          'Kẹo dẻo nhiều hương vị trái cây.'),
(27, 3, 'Socola thanh',                    22000,  35, 'images/banhquy.jpg',       'Socola sữa thanh 40 g.'),
(28, 3, 'Bánh gạo vị ngọt',                11000,  45, 'images/banhtrang.jpeg',    'Bánh gạo nướng vị ngọt.'),
(29, 3, 'Kẹo cao su bạc hà',                6000, 100, 'images/chug.jpg',          'Kẹo cao su bạc hà thanh mát.'),
(30, 3, 'Bánh quy kem sô-cô-la',           14000,  55, 'images/banhquy.jpg',       'Bánh quy kẹp kem sô-cô-la.'),
(31, 4, 'Khăn giấy bỏ túi',                 5000, 120, 'images/MiniPOS.webp',      'Khăn giấy bỏ túi 10 tờ.'),
(32, 4, 'Bàn chải đánh răng',              25000,  30, 'images/MiniPOS.webp',      'Bàn chải lông mềm.'),
(33, 4, 'Kem đánh răng 100 g',             32000,  25, 'images/MiniPOS.webp',      'Kem đánh răng bạc hà 100 g.'),
(34, 4, 'Ly giấy (20 cái)',                15000,  40, 'images/MiniPOS.webp',      'Ly giấy dùng một lần, 20 cái.'),
(35, 4, 'Túi nilon (1 kg)',                10000, 200, 'images/MiniPOS.webp',      'Túi nilon đựng hàng, 1 kg.');

-- Tài khoản thử (mật khẩu: matkhau123): 1 quản trị, 2 khách hàng
INSERT INTO nguoi_dung (id, email, ho_ten, mat_khau, vai_tro) VALUES
(1, 'admin@minipos.vn', 'Quản trị viên',  '$2y$12$FU9nwjXpW5WmbNG9niDgrebwytDPYVtr7xQI0BIr32nmsQmxROXY2', 'quan_tri'),
(2, 'lan@minipos.vn',   'Nguyễn Thị Lan', '$2y$12$j7zLCQ30BXZtr2.dNCKXMOM7zM464Xg/5/pg1aTZk9BygOudr0PSO', 'khach_hang'),
(3, 'an@minipos.vn',    'Trần Văn An',    '$2y$12$DXcUwhVRy06a0e2BfgIjtuaX9wP.68.TTUSPG6K9TxB.su.CVQON.', 'khach_hang');

-- Mỗi khách hàng một đơn. don_gia = giá niêm yết tại thời điểm đặt.
INSERT INTO don_hang (id, nguoi_dung_id, trang_thai, ghi_chu, ngay_dat) VALUES
(1, 2, 'hoan_thanh', 'Giao giờ nghỉ trưa',  '2026-10-20 11:45:00'),
(2, 3, 'cho_xu_ly',  NULL,                  '2026-10-27 08:30:00');

INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia) VALUES
(1, 1,  2, 15000),   -- Bánh snack ×2
(1, 2,  1, 10000),   -- Nước suối ×1
(2, 4,  1, 25000),   -- Trà sữa ×1
(2, 6,  2, 25000),   -- Cà phê ×2
(2, 9,  1, 15000);   -- Nước cam ×1

-- Phiếu liên hệ mẫu (mới nhất có id lớn nhất)
INSERT INTO lien_he (ma_phieu, ma_nhan_vien, email, ngay_bao_cao, muc_do_uu_tien, loai_yeu_cau, noi_dung, thoi_gian_tao) VALUES
('a1b2c3d4e5f60708', 'NV001', 'nv001@minipos.vn', '2026-10-25', 3, 'loi',     'Máy quét mã vạch quầy 2 thỉnh thoảng không nhận mã.', '2026-10-25 09:10:00'),
('0f9e8d7c6b5a4321', 'NV002', 'nv002@minipos.vn', '2026-10-28', 5, 'nhapkho', 'Cà phê sữa lon đã hết, cần nhập thêm gấp.',             '2026-10-28 16:40:00');

-- Dữ liệu mẫu Phần B: thanh_vien và các bảng tv_
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
('3120224122', 'Trần Xuân Sang', 1);

-- Dữ liệu mẫu dự án cá nhân (tv_du_an)
INSERT INTO tv_du_an (mssv, ten_du_an, cong_nghe, mo_ta) VALUES
('3120224122', 'Thùng rác thông minh AI', 'Arduino, ESP8266, Python, YOLO', 'Tự động phân loại rác qua nhận diện hình ảnh và gửi cảnh báo qua ứng dụng Blynk IoT.'),
('3120224122', 'Ứng dụng Chat TCP/UDP', 'Python, Socket, Multi-threading', 'Xây dựng máy chủ và máy khách giao tiếp mạng đa luồng, hỗ trợ broadcast tin nhắn.'),
('3120224122', 'Giải quyết Extended Knapsack', 'C++, Giải thuật PSO & Tham lam', 'Tối ưu hóa bài toán cái túi mở rộng bằng thuật toán bầy đàn (Particle Swarm Optimization).');

-- Dữ liệu mẫu Phần B của Nguyễn Võ Khánh Linh (3120224086)
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
('3120224086', 'Nguyễn Võ Khánh Linh', NULL);

INSERT INTO tv_nhat_ky (mssv, dia_chi_ip, trinh_duyet, thoi_gian_tao) VALUES
('3120224086', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0', '2026-10-27 08:15:00'),
('3120224086', '127.0.0.1', 'Mozilla/5.0 (Android 14) Chrome/130.0', '2026-10-28 09:02:30'),
('3120224086', '::1', 'Mozilla/5.0 (Macintosh) Safari/17', '2026-10-29 20:40:10');