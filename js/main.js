// main.js
// Điều khiển menu điều hướng trên thiết bị di động.

"use strict";

// Đánh dấu trang đang sử dụng JavaScript.
// Khi JavaScript bị tắt, class này không tồn tại
// nên menu vẫn được hiển thị bằng CSS.
document.documentElement.classList.add("js-enabled");

const nutMenu = document.querySelector(".nut-menu");
const menuChinh = document.querySelector("#menu-chinh");

if (nutMenu && menuChinh) {
  nutMenu.addEventListener("click", () => {
    const dangMo = menuChinh.classList.toggle("menu-mo");

    nutMenu.setAttribute("aria-expanded", String(dangMo));
  });

  document.addEventListener("keydown", (suKien) => {
    if (suKien.key !== "Escape") {
      return;
    }

    if (!menuChinh.classList.contains("menu-mo")) {
      return;
    }

    menuChinh.classList.remove("menu-mo");

    nutMenu.setAttribute("aria-expanded", "false");

    nutMenu.focus();
  });
}
// Chương 7 (CSP): thay cho onsubmit="return confirm(…)" và onerror="…" nội tuyến.

// Form có data-xac-nhan="câu hỏi": hỏi lại trước khi gửi.
document.addEventListener("submit", (suKien) => {
  // data-xac-nhan đặt trên nút gửi (ưu tiên) hoặc trên chính form.
  const cauHoi =
    suKien.submitter?.dataset?.xacNhan ?? suKien.target.dataset?.xacNhan;

  if (cauHoi && !window.confirm(cauHoi)) {
    suKien.preventDefault();
  }
});

// Ảnh có data-anh-du-phong="đường dẫn": lỗi tải thì dùng ảnh dự phòng.
// Sự kiện error không nổi bọt nên bắt ở pha capture (cũng áp dụng cho ảnh
// do JavaScript chèn vào sau này).
const dungAnhDuPhong = (anh) => {
  const duPhong = anh.dataset.anhDuPhong;

  if (duPhong && !anh.src.endsWith(duPhong)) {
    anh.src = duPhong;
  }
};

document.addEventListener(
  "error",
  (suKien) => {
    if (suKien.target instanceof HTMLImageElement) {
      dungAnhDuPhong(suKien.target);
    }
  },
  true,
);

// Ảnh đã lỗi trước khi mã này chạy.
document.querySelectorAll("img[data-anh-du-phong]").forEach((anh) => {
  if (anh.complete && anh.naturalWidth === 0) {
    dungAnhDuPhong(anh);
  }
});
