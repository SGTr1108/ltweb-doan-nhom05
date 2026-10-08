// trang-lien-he.js
// Kiểm tra và gửi biểu mẫu liên hệ bằng fetch.

"use strict";

const form = document.querySelector("form");
const nutGui = form.querySelector('button[type="submit"]');

const manv = document.querySelector("#manv");
const email = document.querySelector("#email");
const ngaybc = document.querySelector("#ngaybc");
const mucdo = document.querySelector("#mucdo");
const noidung = document.querySelector("#noidung");

const loiManv = document.querySelector("#loi-manv");
const loiEmail = document.querySelector("#loi-email");
const loiNgaybc = document.querySelector("#loi-ngaybc");
const loiMucdo = document.querySelector("#loi-mucdo");
const loiNoidung = document.querySelector("#loi-noidung");

const trangThai = document.querySelector("#trang-thai-lien-he");
const nutThuLai = document.querySelector("#thu-lai-lien-he");
nutThuLai.style.display = "none"; 

let duLieuGui = null;

function hienThiLoi(oInput, oLoi, noiDung) {
    oLoi.textContent = noiDung;
    oInput.setAttribute("aria-invalid", "true");
}

function xoaLoi(oInput, oLoi) {
    oLoi.textContent = "";
    oInput.removeAttribute("aria-invalid");
}

function kiemTraManv() {
    if (manv.validity.valueMissing) {
        hienThiLoi(manv, loiManv, "Vui lòng nhập mã nhân viên.");
        manv.setCustomValidity("Vui lòng nhập mã nhân viên.");
        return false;
    }

    if (manv.validity.patternMismatch) {
        hienThiLoi(
            manv,
            loiManv,
            "Mã nhân viên phải có dạng NV001."
        );
        manv.setCustomValidity("Mã nhân viên không đúng định dạng.");
        return false;
    }

    manv.setCustomValidity("");
    xoaLoi(manv, loiManv);
    return true;
}

function kiemTraEmail() {
    if (email.validity.valueMissing) {
        hienThiLoi(email, loiEmail, "Vui lòng nhập email.");
        email.setCustomValidity("Vui lòng nhập email.");
        return false;
    }

    if (email.validity.typeMismatch) {
        hienThiLoi(email, loiEmail, "Email không đúng định dạng.");
        email.setCustomValidity("Email không đúng định dạng.");
        return false;
    }

    email.setCustomValidity("");
    xoaLoi(email, loiEmail);
    return true;
}

function kiemTraNgay() {
    if (ngaybc.validity.valueMissing) {
        hienThiLoi(
            ngaybc,
            loiNgaybc,
            "Vui lòng chọn ngày báo cáo."
        );
        ngaybc.setCustomValidity("Vui lòng chọn ngày báo cáo.");
        return false;
    }

    ngaybc.setCustomValidity("");
    xoaLoi(ngaybc, loiNgaybc);
    return true;
}

function kiemTraMucDo() {
    if (mucdo.validity.valueMissing) {
        hienThiLoi(
            mucdo,
            loiMucdo,
            "Vui lòng nhập mức độ khẩn cấp."
        );
        mucdo.setCustomValidity("Vui lòng nhập mức độ khẩn cấp.");
        return false;
    }

    if (
        mucdo.validity.badInput ||
        mucdo.validity.rangeUnderflow ||
        mucdo.validity.rangeOverflow
    ) {
        hienThiLoi(
            mucdo,
            loiMucdo,
            "Mức độ khẩn cấp phải là số từ 1 đến 5."
        );
        mucdo.setCustomValidity(
            "Mức độ khẩn cấp phải là số từ 1 đến 5."
        );
        return false;
    }

    mucdo.setCustomValidity("");
    xoaLoi(mucdo, loiMucdo);
    return true;
}

function kiemTraNoiDung() {
    if (noidung.validity.valueMissing) {
        hienThiLoi(
            noidung,
            loiNoidung,
            "Vui lòng nhập nội dung báo cáo."
        );
        noidung.setCustomValidity("Vui lòng nhập nội dung báo cáo.");
        return false;
    }

    noidung.setCustomValidity("");
    xoaLoi(noidung, loiNoidung);
    return true;
}

const cacKiemTra = [
    [manv, kiemTraManv],
    [email, kiemTraEmail],
    [ngaybc, kiemTraNgay],
    [mucdo, kiemTraMucDo],
    [noidung, kiemTraNoiDung]
];

cacKiemTra.forEach(([oInput, hamKiemTra]) => {
    oInput.addEventListener("blur", hamKiemTra);
});

function kiemTraTatCa() {
    const hopLeManv = kiemTraManv();
    const hopLeEmail = kiemTraEmail();
    const hopLeNgay = kiemTraNgay();
    const hopLeMucDo = kiemTraMucDo();
    const hopLeNoiDung = kiemTraNoiDung();

    return (
        hopLeManv &&
        hopLeEmail &&
        hopLeNgay &&
        hopLeMucDo &&
        hopLeNoiDung
    );
}

async function guiPhieuBaoCao() {
    try {
        trangThai.textContent = "Đang gửi phiếu báo cáo...";
        nutGui.disabled = true;
        nutThuLai.style.display = "none";

        const res = await fetch(
            "https://jsonplaceholder.typicode.com/posts",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(duLieuGui)
            }
        );

        if (!res.ok) {
            throw new Error(`Gửi dữ liệu thất bại: ${res.status}`);
        }

        const ketQua = await res.json();

        console.log("Dữ liệu đã gửi:", ketQua);

        trangThai.textContent =
            "Gửi phiếu báo cáo thành công.";

        form.reset();
    } catch (loi) {
        console.error(loi);

        trangThai.textContent =
            "Không thể gửi phiếu báo cáo. Vui lòng thử lại.";

        nutThuLai.style.display = "inline-block";
    } finally {
        nutGui.disabled = false;
    }
}

form.addEventListener("submit", (suKien) => {
    suKien.preventDefault();

    if (!kiemTraTatCa()) {
        trangThai.textContent =
            "Vui lòng kiểm tra lại thông tin trong biểu mẫu.";
        return;
    }

    const formData = new FormData(form);

    duLieuGui = Object.fromEntries(formData.entries());

    guiPhieuBaoCao();
});

nutThuLai.addEventListener("click", guiPhieuBaoCao);