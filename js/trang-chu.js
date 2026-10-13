// trang-chu.js
// Tải và hiển thị dữ liệu thời tiết từ REST API công khai.

"use strict";

import { taiJSON } from "./api.js";

const trangThai = document.querySelector("#trang-thai-thoi-tiet");
const khuVucDuLieu = document.querySelector("#du-lieu-thoi-tiet");
const nutThuLai = document.querySelector("#thu-lai-thoi-tiet");

const URL_THOI_TIET =
    "https://api.open-meteo.com/v1/forecast" +
    "?latitude=21.0285" +
    "&longitude=105.8542" +
    "&current=temperature_2m,relative_humidity_2m,wind_speed_10m" +
    "&timezone=Asia%2FBangkok";

async function taiThoiTiet() {
    try {
        trangThai.textContent = "Đang tải dữ liệu thời tiết...";
        nutThuLai.hidden = true;
        khuVucDuLieu.replaceChildren();

        const duLieu = await taiJSON(URL_THOI_TIET);

        if (!duLieu.current) {
            throw new Error("Dữ liệu thời tiết không có thông tin hiện tại.");
        }

        hienThiThoiTiet(duLieu);

    } catch (loi) {
        console.error(loi);

        trangThai.textContent =
            "Không thể tải dữ liệu thời tiết. Vui lòng thử lại.";

        nutThuLai.hidden = false;
    }
}

function hienThiThoiTiet(duLieu) {
    const current = duLieu.current;
    const units = duLieu.current_units;

    khuVucDuLieu.replaceChildren();

    const tieuDe = document.createElement("h3");
    tieuDe.textContent = "Thời tiết hiện tại";

    const nhietDo = document.createElement("p");
    nhietDo.textContent =
        `Nhiệt độ: ${current.temperature_2m} ${units.temperature_2m}`;

    const doAm = document.createElement("p");
    doAm.textContent =
        `Độ ẩm: ${current.relative_humidity_2m} ${units.relative_humidity_2m}`;

    const gio = document.createElement("p");
    gio.textContent =
        `Tốc độ gió: ${current.wind_speed_10m} ${units.wind_speed_10m}`;

    const thoiGian = document.createElement("p");
    thoiGian.textContent =
        `Thời gian cập nhật: ${current.time}`;

    khuVucDuLieu.append(
        tieuDe,
        nhietDo,
        doAm,
        gio,
        thoiGian
    );

    trangThai.textContent = "Đã tải dữ liệu thời tiết.";
}

nutThuLai.addEventListener("click", taiThoiTiet);

taiThoiTiet();