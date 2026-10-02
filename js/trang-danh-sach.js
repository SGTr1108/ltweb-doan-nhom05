import { taiJSON } from "./api.js";

import {
    laYeuThich,
    capNhatNutYeuThich
} from "./yeu-thich.js";

"use strict";

const danhSachSanPham = document.querySelector(".luoi-san-pham");
const oTimKiem = document.querySelector("#tim-kiem");
const oLocDanhMuc = document.querySelector("#loc-danh-muc");
const oSapXep = document.querySelector("#sap-xep");
const trangThai = document.querySelector("#trang-thai-san-pham");
const nutThuLai = document.querySelector("#thu-lai-san-pham");

let sanPham = [];

async function taiSanPham() {
    try {
        trangThai.textContent = "Đang tải danh sách sản phẩm...";
        nutThuLai.hidden = true;

        sanPham = await taiJSON("data/san-pham.json");

        hienThiDanhMuc();
        hienThiSanPham(sanPham);
    } catch (loi) {
        console.error(loi);

        trangThai.textContent =
            "Không thể tải danh sách sản phẩm. Vui lòng thử lại.";

        nutThuLai.hidden = false;
    }
}

function hienThiDanhMuc() {
    oLocDanhMuc.replaceChildren();

    const optionTatCa = document.createElement("option");
    optionTatCa.value = "tat-ca";
    optionTatCa.textContent = "Tất cả danh mục";
    oLocDanhMuc.appendChild(optionTatCa);

    const danhMuc = [...new Set(
        sanPham.map((sp) => sp.danhMuc)
    )];

    danhMuc.forEach((muc) => {
        const option = document.createElement("option");

        option.value = muc;
        option.textContent = muc;

        oLocDanhMuc.appendChild(option);
    });
}

function hienThiSanPham(danhSach) {
    danhSachSanPham.replaceChildren();

    if (danhSach.length === 0) {
        trangThai.textContent =
            "Không tìm thấy sản phẩm phù hợp.";
        return;
    }

    trangThai.textContent =
        `Có ${danhSach.length} sản phẩm.`;

    danhSach.forEach((sp) => {
        const article = document.createElement("article");
        article.className = "the-san-pham";

        const anh = document.createElement("img");
        anh.src = sp.anh;
        anh.alt = `Hình ảnh ${sp.ten}`;
        anh.width = 800;
        anh.height = 533;

        const noiDung = document.createElement("div");
        noiDung.className = "noi-dung-san-pham";

        const tieuDe = document.createElement("h3");
        tieuDe.textContent = sp.ten;

        const ma = document.createElement("p");
        ma.className = "ma-san-pham";
        ma.textContent =
            `Mã sản phẩm: ${sp.id}`;

        const gia = document.createElement("p");
        gia.className = "gia-san-pham";
        gia.textContent =
            `${sp.gia.toLocaleString("vi-VN")}đ`;

        const tonKho = document.createElement("p");
        tonKho.textContent =
            `Tồn kho: ${sp.tonKho}`;

        const chiTiet = document.createElement("a");
        chiTiet.href =
            `chi-tiet.html?id=${encodeURIComponent(sp.id)}`;
        chiTiet.textContent = "Xem chi tiết";

        const nutYeuThich = document.createElement("button");

        nutYeuThich.type = "button";
        nutYeuThich.className = "nut-yeu-thich";
        nutYeuThich.dataset.yeuThich = sp.id;

        const dangYeuThich = laYeuThich(sp.id);

        nutYeuThich.setAttribute(
            "aria-pressed",
            String(dangYeuThich)
        );

        nutYeuThich.textContent = dangYeuThich
            ? "♥ Bỏ yêu thích"
            : "♡ Yêu thích";

            const hangNut = document.createElement("div");
            hangNut.className = "hang-nut-san-pham";
            
            hangNut.append(
                chiTiet,
                nutYeuThich
            );
            
            noiDung.append(
                tieuDe,
                ma,
                gia,
                tonKho,
                hangNut
            );

        article.append(
            anh,
            noiDung
        );

        danhSachSanPham.appendChild(article);
    });

    capNhatNutYeuThich();
}

function boDau(chuoi) {
    return chuoi
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/Đ/g, "D");
}

function locVaSapXep() {
    const tuKhoa =
        boDau(
            oTimKiem.value
                .trim()
                .toLowerCase()
        );

    const danhMuc = oLocDanhMuc.value;
    const cachSapXep = oSapXep.value;

    let ketQua = sanPham.filter((sp) => {
        const phuHopTuKhoa =
            boDau(sp.ten.toLowerCase())
                .includes(tuKhoa) ||
            sp.id.toLowerCase()
                .includes(tuKhoa);

        const phuHopDanhMuc =
            danhMuc === "tat-ca" ||
            sp.danhMuc === danhMuc;

        return phuHopTuKhoa && phuHopDanhMuc;
    });

    ketQua.sort((a, b) => {
        if (cachSapXep === "gia-tang") {
            return a.gia - b.gia;
        }

        if (cachSapXep === "gia-giam") {
            return b.gia - a.gia;
        }

        if (cachSapXep === "ten-az") {
            return a.ten.localeCompare(b.ten, "vi");
        }

        if (cachSapXep === "ten-za") {
            return b.ten.localeCompare(a.ten, "vi");
        }

        return 0;
    });

    hienThiSanPham(ketQua);
}

oTimKiem.addEventListener(
    "input",
    locVaSapXep
);

oLocDanhMuc.addEventListener(
    "change",
    locVaSapXep
);

oSapXep.addEventListener(
    "change",
    locVaSapXep
);

nutThuLai.addEventListener(
    "click",
    taiSanPham
);

taiSanPham();