-- database/schema.sql — tạo lại CSDL từ đầu (chạy lại được nhiều lần)
-- Dự án: MiniPos (nhóm 05) — Bài tập nhóm số 6, Phần A
-- Chạy:  mysql -u root < database/schema.sql
-- Quy ước: tên bảng/cột chữ thường không dấu nối bằng _, bảng số ít,
--          khóa chính id, khóa ngoại <bảng>_id.

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS ltweb_nhom05;
CREATE DATABASE ltweb_nhom05
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ltweb_nhom05;

-- ---------------------------------------------------------------------
-- 1. danh_muc — nhóm hàng (Đồ ăn, Đồ uống…). Quan hệ 1 – n với san_pham.
-- ---------------------------------------------------------------------
CREATE TABLE danh_muc (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten  VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_danh_muc_slug (slug)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. san_pham — thực thể chính.
--    Không ghi ON DELETE cho danh_muc_id: MySQL cấm xóa danh mục còn
--    sản phẩm (lỗi 1451) — chủ ý, tránh mất hàng khi xóa nhầm danh mục.
--    Tiền dùng INT UNSIGNED (đồng, không có phần lẻ), không dùng FLOAT.
-- ---------------------------------------------------------------------
CREATE TABLE san_pham (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    danh_muc_id INT UNSIGNED NOT NULL,
    ten         VARCHAR(200) NOT NULL,
    gia         INT UNSIGNED NOT NULL CHECK (gia >= 1000),      -- đồng
    ton_kho     INT UNSIGNED NOT NULL DEFAULT 0,
    anh         VARCHAR(255) NULL,                              -- đường dẫn ảnh
    mo_ta       TEXT NULL,
    ngay_tao    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_san_pham_danh_muc
        FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. nguoi_dung — tài khoản. mat_khau chỉ lưu chuỗi băm password_hash
--    (bcrypt: 60 ký tự; để VARCHAR(255) cho phép đổi thuật toán sau này).
--    email UNIQUE (collation _ci nên A@x.vn và a@x.vn coi là trùng).
-- ---------------------------------------------------------------------
CREATE TABLE nguoi_dung (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email    VARCHAR(100) NOT NULL,
    ho_ten   VARCHAR(100) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    vai_tro  ENUM('quan_tri', 'khach_hang') NOT NULL DEFAULT 'khach_hang',
    ngay_tao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nguoi_dung_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. don_hang — mỗi đơn thuộc một người dùng (1 – n).
--    Không có cột tong_tien: tổng = SUM(so_luong * don_gia) từ
--    chi_tiet_don (tránh dữ liệu dẫn xuất lệch nhau — chuẩn hóa 3NF).
--    trang_thai theo bản đồ trạng thái: cho_xu_ly → dang_giao →
--    hoan_thanh; cho_xu_ly / dang_giao → da_huy.
-- ---------------------------------------------------------------------
CREATE TABLE don_hang (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id INT UNSIGNED NOT NULL,
    trang_thai    ENUM('cho_xu_ly', 'dang_giao', 'hoan_thanh', 'da_huy')
                  NOT NULL DEFAULT 'cho_xu_ly',
    ghi_chu       VARCHAR(255) NULL,
    ngay_dat      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_don_hang_nguoi_dung
        FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id),
    KEY idx_don_hang_ngay_dat (ngay_dat)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. chi_tiet_don — bảng trung gian của quan hệ n – n don_hang ↔ san_pham,
--    khóa chính ghép (don_hang_id, san_pham_id).
--    don_gia: GIÁ LÚC ĐẶT, cố ý lưu lặp — giá niêm yết (san_pham.gia) có
--    thể đổi sau này, nhưng đơn đã đặt phải giữ nguyên số tiền.
--    Xóa đơn → xóa chi tiết (CASCADE); xóa sản phẩm đã có trong đơn bị
--    cấm (lỗi 1451) để không mất lịch sử bán hàng.
-- ---------------------------------------------------------------------
CREATE TABLE chi_tiet_don (
    don_hang_id INT UNSIGNED NOT NULL,
    san_pham_id INT UNSIGNED NOT NULL,
    so_luong    SMALLINT UNSIGNED NOT NULL CHECK (so_luong >= 1),
    don_gia     INT UNSIGNED NOT NULL CHECK (don_gia >= 1000),
    PRIMARY KEY (don_hang_id, san_pham_id),
    CONSTRAINT fk_chi_tiet_don_don_hang
        FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
    CONSTRAINT fk_chi_tiet_don_san_pham
        FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. lien_he — phiếu báo cáo của trang lien-he.php (thay storage/lien-he.jsonl)
--    Giữ đủ các trường của form Bài tập 5.
-- ---------------------------------------------------------------------
CREATE TABLE lien_he (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_phieu       CHAR(16) NOT NULL,
    ma_nhan_vien   VARCHAR(10) NOT NULL,                 -- dạng NV001
    email          VARCHAR(100) NOT NULL,
    ngay_bao_cao   DATE NOT NULL,
    muc_do_uu_tien TINYINT UNSIGNED NOT NULL CHECK (muc_do_uu_tien BETWEEN 1 AND 5),
    loai_yeu_cau   ENUM('loi', 'nhapkho') NOT NULL,
    noi_dung       TEXT NOT NULL,
    anh_dinh_kem   VARCHAR(255) NULL,                    -- tên tệp trong uploads/
    thoi_gian_tao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lien_he_ma_phieu (ma_phieu),
    KEY idx_lien_he_thoi_gian_tao (thoi_gian_tao)
) ENGINE=InnoDB;

-- =================================================--------------------
-- Phần B: Bảng thanh_vien và các bảng riêng của thành viên (tv_...)
-- =================================================--------------------

-- 1. Bảng thanh_vien: Lưu danh sách thành viên nhóm
CREATE TABLE thanh_vien (
    mssv          VARCHAR(15) PRIMARY KEY,
    ho_ten        VARCHAR(100) NOT NULL,
    nguoi_dung_id INT UNSIGNED NULL,
    CONSTRAINT fk_thanh_vien_nguoi_dung
        FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 2. Bảng riêng của Trần Xuân Sang (MSSV: 3120224122): tv_luu_but
CREATE TABLE tv_luu_but (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv           VARCHAR(15) NOT NULL,
    ten_nguoi_gui  VARCHAR(100) NOT NULL,
    loi_nhan       TEXT NOT NULL,
    thoi_gian_tao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tv_luu_but_thanh_vien
        FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE,
    KEY idx_tv_luu_but_thoi_gian (thoi_gian_tao)
) ENGINE=InnoDB;

-- 3. Bảng riêng của Trần Xuân Sang (MSSV: 3120224122): tv_du_an
CREATE TABLE tv_du_an (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv          VARCHAR(15) NOT NULL,
    ten_du_an     VARCHAR(200) NOT NULL,
    cong_nghe     VARCHAR(255) NOT NULL,
    mo_ta         TEXT NOT NULL,
    ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tv_du_an_thanh_vien
        FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4Bảng riêng của Nguyễn Võ Khánh Linh (3120224086): tv_nhat_ky
CREATE TABLE tv_nhat_ky (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv           VARCHAR(15) NOT NULL,
    dia_chi_ip     VARCHAR(45) NOT NULL,
    trinh_duyet    VARCHAR(255) NOT NULL,
    thoi_gian_tao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tv_nhat_ky_thanh_vien
        FOREIGN KEY (mssv) REFERENCES thanh_vien(mssv) ON DELETE CASCADE,
    KEY idx_tv_nhat_ky_mssv_thoi_gian (mssv, thoi_gian_tao)
) ENGINE=InnoDB;

-- 5. Bảng riêng của Nguyễn Võ Khánh Linh: tv_luot_xem
CREATE TABLE tv_luot_xem (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mssv           VARCHAR(15) NOT NULL,
    ngay_xem       DATE NOT NULL,
    so_luot        INT UNSIGNED NOT NULL DEFAULT 0,

    CONSTRAINT fk_tv_luot_xem_thanh_vien
        FOREIGN KEY (mssv)
        REFERENCES thanh_vien(mssv)
        ON DELETE CASCADE,

    UNIQUE KEY uq_tv_luot_xem_mssv_ngay (mssv, ngay_xem)
) ENGINE=InnoDB;