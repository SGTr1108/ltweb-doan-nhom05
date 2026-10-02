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

        nutMenu.setAttribute(
            "aria-expanded",
            String(dangMo)
        );
    });

    document.addEventListener("keydown", (suKien) => {
        if (suKien.key !== "Escape") {
            return;
        }

        if (!menuChinh.classList.contains("menu-mo")) {
            return;
        }

        menuChinh.classList.remove("menu-mo");

        nutMenu.setAttribute(
            "aria-expanded",
            "false"
        );

        nutMenu.focus();
    });
}