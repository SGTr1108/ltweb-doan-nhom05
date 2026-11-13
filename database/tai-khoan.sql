-- Tạo tài khoản riêng cho ứng dụng MiniPos.
-- Thay CHUOI_GIU_CHO_NAY bằng mật khẩu riêng trước khi thực thi.

CREATE USER IF NOT EXISTS 'nhom05_app'@'localhost'
    IDENTIFIED BY 'CHUOI_GIU_CHO_NAY';

-- Chỉ cấp 4 quyền thao tác dữ liệu trên CSDL của nhóm.
GRANT SELECT, INSERT, UPDATE, DELETE
    ON `ltweb_nhom05`.* TO 'nhom05_app'@'localhost';

FLUSH PRIVILEGES;

-- Kiểm tra quyền:
SHOW GRANTS FOR 'nhom05_app'@'localhost';
