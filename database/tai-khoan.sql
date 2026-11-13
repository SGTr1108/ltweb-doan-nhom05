CREATE USER IF NOT EXISTS 'root'@'localhost'
    IDENTIFIED BY '';

-- Chỉ cấp 4 quyền thao tác dữ liệu
GRANT SELECT, INSERT, UPDATE, DELETE
    ON `ltweb_nhom05`.* TO 'root'@'localhost';

FLUSH PRIVILEGES;

-- Kiểm tra: SHOW GRANTS FOR 'nhom05_app'@'localhost';
