"use strict";

const form = document.querySelector(".bo-loc-san-pham");
const oTimKiem = document.querySelector("#tim-kiem");
const oDanhMuc = document.querySelector("#loc-danh-muc");
const oSapXep = document.querySelector("#sap-xep");
const luoi = document.querySelector(".luoi-san-pham");
const trangThai = document.querySelector("#trang-thai-san-pham");

if (form && oTimKiem && oDanhMuc && oSapXep && luoi && trangThai) {
    const cacThe = Array.from(luoi.querySelectorAll(".the-san-pham"));
    const thuTuGoc = new Map(cacThe.map((the, index) => [the, index]));

    function boDau(chuoi) {
        return chuoi
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/đ/g, "d")
            .replace(/Đ/g, "D")
            .toLocaleLowerCase("vi");
    }

    function capNhatDanhSach() {
        const tuKhoa = boDau(oTimKiem.value.trim());
        const danhMuc = oDanhMuc.value;
        const cachSapXep = oSapXep.value;

        const daSapXep = [...cacThe];

        daSapXep.sort((a, b) => {
            const giaA = Number(a.dataset.gia || 0);
            const giaB = Number(b.dataset.gia || 0);
            const tenA = a.dataset.ten || "";
            const tenB = b.dataset.ten || "";

            switch (cachSapXep) {
                case "gia-tang":
                    return giaA - giaB;
                case "gia-giam":
                    return giaB - giaA;
                case "ten-az":
                    return tenA.localeCompare(tenB, "vi");
                case "ten-za":
                    return tenB.localeCompare(tenA, "vi");
                default:
                    return thuTuGoc.get(a) - thuTuGoc.get(b);
            }
        });

        let soLuongHienThi = 0;

        daSapXep.forEach((the) => {
            const ten = boDau(the.dataset.ten || "");
            const ma = boDau(the.dataset.ma || "");
            const muc = the.dataset.danhMuc || "";

            const khopTuKhoa =
                tuKhoa === "" ||
                ten.includes(tuKhoa) ||
                ma.includes(tuKhoa);

            const khopDanhMuc =
                danhMuc === "tat-ca" ||
                muc === danhMuc;

            const hienThi = khopTuKhoa && khopDanhMuc;

            the.style.display = hienThi ? "" : "none";

            // Dua cac the ve dung thu tu sap xep.
            luoi.appendChild(the);

            if (hienThi) {
                soLuongHienThi++;
            }
        });

        trangThai.textContent = soLuongHienThi === 0
            ? "Không tìm thấy sản phẩm phù hợp."
            : `Có ${soLuongHienThi} sản phẩm phù hợp.`;
    }

    // Loc ngay khi nguoi dung go, khong can bam Ap dung.
    oTimKiem.addEventListener("input", capNhatDanhSach);
    oDanhMuc.addEventListener("change", capNhatDanhSach);
    oSapXep.addEventListener("change", capNhatDanhSach);


    // Khi nhan Ap dung, de trinh duyet gui form GET len PHP.
    // Khong goi preventDefault() de URL luu lai bo loc.
    form.addEventListener("submit", () => {
        // De trong: trinh duyet tu gui form theo phuong thuc GET.
    });

    capNhatDanhSach();
}