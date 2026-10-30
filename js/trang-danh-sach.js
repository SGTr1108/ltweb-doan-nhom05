"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("#bo-loc-san-pham");
    const inputTimKiem = document.querySelector("#tim-kiem");
    const selectDanhMuc = document.querySelector("#loc-danh-muc");
    const inputGiaToiDa = document.querySelector("#gia-toi-da");
    const selectSapXep = document.querySelector("#sap-xep");
    const luoi = document.querySelector("#luoi-san-pham");
    const trangThai = document.querySelector("#trang-thai-san-pham");

    if (!form || !inputTimKiem || !selectDanhMuc || !inputGiaToiDa || !selectSapXep || !luoi || !trangThai) return;

    // Chỉ lấy các thẻ gốc một lần; lọc bằng ẩn/hiện nên nút thêm giỏ không bị mất.
    const cacThe = Array.from(luoi.querySelectorAll(".the-san-pham"));
    const thuTuGoc = new Map(cacThe.map((the, index) => [the, index]));

    const boDau = (value) => String(value ?? "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/Đ/g, "D")
        .toLocaleLowerCase("vi");

    function capNhatDanhSach() {
        const tuKhoa = boDau(inputTimKiem.value.trim());
        const danhMuc = selectDanhMuc.value;
        const giaNhap = inputGiaToiDa.value.trim();
        const giaToiDa = giaNhap === "" ? null : Number(giaNhap);
        const cachSapXep = selectSapXep.value;

        const daSapXep = [...cacThe];
        daSapXep.sort((a, b) => {
            const giaA = Number(a.dataset.gia || 0);
            const giaB = Number(b.dataset.gia || 0);
            const tenA = a.dataset.ten || "";
            const tenB = b.dataset.ten || "";
            switch (cachSapXep) {
                case "gia-tang": return giaA - giaB;
                case "gia-giam": return giaB - giaA;
                case "ten": return tenA.localeCompare(tenB, "vi");
                default: return thuTuGoc.get(a) - thuTuGoc.get(b);
            }
        });

        let dem = 0;
        daSapXep.forEach((the) => {
            const ten = boDau(the.dataset.ten);
            const ma = boDau(the.dataset.ma);
            const gia = Number(the.dataset.gia || 0);
            const muc = the.dataset.danhMuc || "";

            const khopTuKhoa = !tuKhoa || ten.includes(tuKhoa) || ma.includes(tuKhoa);
            const khopDanhMuc = !danhMuc || muc === danhMuc;
            const khopGia = giaToiDa === null || (Number.isFinite(giaToiDa) && gia <= giaToiDa);
            const hienThi = khopTuKhoa && khopDanhMuc && khopGia;

            the.hidden = !hienThi;
            luoi.appendChild(the);
            if (hienThi) dem++;
        });

        trangThai.textContent = dem === 0
            ? "Không tìm thấy sản phẩm phù hợp."
            : `Có ${dem} sản phẩm phù hợp.`;
    }

    // Tự lọc ngay khi nhập/chọn, không cần bấm nút Áp dụng.
    inputTimKiem.addEventListener("input", capNhatDanhSach);
    selectDanhMuc.addEventListener("change", capNhatDanhSach);
    inputGiaToiDa.addEventListener("input", capNhatDanhSach);
    selectSapXep.addEventListener("change", capNhatDanhSach);

    // Ngăn submit làm tải lại trang; bộ lọc hoạt động hoàn toàn trên 35 thẻ đã tải.
    form.addEventListener("submit", (event) => {
        event.preventDefault();
        capNhatDanhSach();
    });

    // Áp dụng sẵn tham số GET nếu người dùng mở URL có bộ lọc.
    capNhatDanhSach();
});
