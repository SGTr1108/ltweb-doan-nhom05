// trang-chi-tiet.js
// Đọc id trên URL và hiển thị thông tin sản phẩm tương ứng.

"use strict";

import { taiJSON } from "./api.js";

import {
    laYeuThich,
    capNhatNutYeuThich
} from "./yeu-thich.js";

const khuVucChiTiet = document.querySelector("#chi-tiet-san-pham");
const thongTinNhanh = document.querySelector("#thong-tin-nhanh");
const nutYeuThich = document.querySelector("#nut-yeu-thich-chi-tiet");

async function taiChiTietSanPham() {
    try {
        khuVucChiTiet.textContent =
            "Đang tải thông tin sản phẩm...";

        const thamSo =
            new URLSearchParams(window.location.search);

        const idSanPham =
            thamSo.get("id");

        if (!idSanPham) {
            khuVucChiTiet.textContent =
                "Không tìm thấy mã sản phẩm.";

            thongTinNhanh.replaceChildren();

            return;
        }

        const danhSachSanPham =
            await taiJSON("data/san-pham.json");

        const sanPham =
            danhSachSanPham.find(
                (sp) => sp.id === idSanPham
            );

        if (!sanPham) {
            khuVucChiTiet.textContent =
                "Không tìm thấy sản phẩm phù hợp.";

            thongTinNhanh.replaceChildren();

            return;
        }

        hienThiChiTiet(sanPham);

    } catch (loi) {
        console.error(loi);

        khuVucChiTiet.textContent =
            "Không thể tải thông tin sản phẩm. Vui lòng thử lại.";
    }
}

function hienThiChiTiet(sp) {
    // Đổi tiêu đề tab theo tên sản phẩm
    document.title = `${sp.ten} - MiniPos`;

    khuVucChiTiet.replaceChildren();

    const tieuDe =
        document.createElement("h1");

    tieuDe.textContent =
        `Chi tiết sản phẩm: ${sp.ten}`;

    const anh =
        document.createElement("img");

    anh.src = sp.anh;
    anh.alt = `Hình ảnh ${sp.ten}`;
    anh.width = 350;
    anh.height = 400;

    const ma =
        document.createElement("p");

    ma.textContent =
        `Mã sản phẩm: ${sp.id}`;

    const gia =
        document.createElement("p");

    gia.textContent =
        `Giá bán: ${sp.gia.toLocaleString("vi-VN")}đ`;

    const danhMuc =
        document.createElement("p");

    danhMuc.textContent =
        `Danh mục: ${sp.danhMuc}`;

    const tonKho =
        document.createElement("p");

    tonKho.textContent =
        `Tồn kho: ${sp.tonKho}`;

    // Gắn mã sản phẩm vào nút yêu thích
    nutYeuThich.dataset.yeuThich =
        sp.id;

    khuVucChiTiet.append(
        tieuDe,
        anh,
        ma,
        gia,
        danhMuc,
        tonKho
    );

    thongTinNhanh.replaceChildren();

    const thongTin = [
        `Mã sản phẩm: ${sp.id}`,
        `Tên sản phẩm: ${sp.ten}`,
        `Giá bán: ${sp.gia.toLocaleString("vi-VN")}đ`,
        `Tồn kho: ${sp.tonKho}`,
        `Danh mục: ${sp.danhMuc}`
    ];

    thongTin.forEach((noiDung) => {
        const muc =
            document.createElement("li");

        muc.textContent =
            noiDung;

        thongTinNhanh.appendChild(muc);
    });

    // Cập nhật trạng thái nút yêu thích
    const dangYeuThich =
        laYeuThich(sp.id);

    nutYeuThich.setAttribute(
        "aria-pressed",
        String(dangYeuThich)
    );

    nutYeuThich.textContent =
        dangYeuThich
            ? "♥ Bỏ yêu thích"
            : "♡ Yêu thích";

    capNhatNutYeuThich();
}

taiChiTietSanPham();