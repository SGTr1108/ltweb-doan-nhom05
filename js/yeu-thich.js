"use strict";

const TEN_KHOA = "sanPhamYeuThich";
const SELECTOR_NUT_YEU_THICH = "[data-yeu-thich]";
const SELECTOR_SO_LUONG = "[data-so-luong-yeu-thich]";

function layDanhSachYeuThich() {
    try {
        const duLieu = localStorage.getItem(TEN_KHOA);

        if (!duLieu) {
            return [];
        }

        const danhSach = JSON.parse(duLieu);

        return Array.isArray(danhSach) ? danhSach : [];
    } catch (loi) {
        console.error(loi);
        return [];
    }
}

function luuDanhSachYeuThich(danhSach) {
    localStorage.setItem(
        TEN_KHOA,
        JSON.stringify(danhSach)
    );
}

function capNhatSoLuongYeuThich() {
    const danhSach = layDanhSachYeuThich();

    document
        .querySelectorAll(SELECTOR_SO_LUONG)
        .forEach((phanTu) => {
            phanTu.textContent = String(danhSach.length);
        });
}

function laYeuThich(idSanPham) {
    const danhSach = layDanhSachYeuThich();

    return danhSach.includes(idSanPham);
}

function doiTrangThaiYeuThich(idSanPham) {
    const danhSach = layDanhSachYeuThich();
    const viTri = danhSach.indexOf(idSanPham);

    if (viTri === -1) {
        danhSach.push(idSanPham);
    } else {
        danhSach.splice(viTri, 1);
    }

    luuDanhSachYeuThich(danhSach);
    capNhatSoLuongYeuThich();
}

function capNhatNutYeuThich() {
    document
        .querySelectorAll(SELECTOR_NUT_YEU_THICH)
        .forEach((nut) => {
            const idSanPham = nut.dataset.yeuThich;
            const dangYeuThich = laYeuThich(idSanPham);

            nut.setAttribute(
                "aria-pressed",
                String(dangYeuThich)
            );

            nut.textContent = dangYeuThich
                ? "♥ Bỏ yêu thích"
                : "♡ Yêu thích";
        });
}

document.addEventListener("click", (suKien) => {
    const nut = suKien.target.closest(SELECTOR_NUT_YEU_THICH);

    if (!nut) {
        return;
    }

    const idSanPham = nut.dataset.yeuThich;

    if (!idSanPham) {
        return;
    }

    doiTrangThaiYeuThich(idSanPham);
    capNhatNutYeuThich();
});

capNhatSoLuongYeuThich();
capNhatNutYeuThich();

export {
    layDanhSachYeuThich,
    luuDanhSachYeuThich,
    laYeuThich,
    doiTrangThaiYeuThich,
    capNhatSoLuongYeuThich,
    capNhatNutYeuThich
};