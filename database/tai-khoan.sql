CREATE USER IF NOT EXISTS 'nhom05_app'@'localhost'
    IDENTIFIED BY '123456';

-- Chỉ cấp 4 quyền thao tác dữ liệu
GRANT SELECT, INSERT, UPDATE, DELETE
    ON `ltweb_nhom05`.* TO 'nhom05_app'@'localhost';

FLUSH PRIVILEGES;

-- Kiểm tra: SHOW GRANTS FOR 'nhom05_app'@'localhost';
