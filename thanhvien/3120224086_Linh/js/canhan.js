/* Tên tệp: canhan.js
 * Mô tả: Xử lý tương tác trang cá nhân gồm đồng hồ thực, lời chào theo giờ,
 *        và cơ chế đổi giao diện sáng/tối đồng bộ Cookie mượt mà.
 * Cách thử: Mở gioithieu.php, quan sát lời chào, đồng hồ và bấm nút chuyển giao diện.
 */
document.addEventListener("DOMContentLoaded", () => {
  const khuTuongTac = document.querySelector(".tuong-tac-ca-nhan");
  const loiChao = document.getElementById("loi-chao");
  const nut = document.getElementById("nut-giao-dien");

  /* ---------- Đồng hồ: tạo phần tử mới bằng createElement ---------- */
  let dongHo = null;
  if (khuTuongTac) {
    dongHo = document.createElement("small");
    dongHo.id = "dong-ho";
    dongHo.className = "dong-ho";
    dongHo.style.marginLeft = "auto";
    khuTuongTac.appendChild(dongHo);
  }

  /* ---------- Lời chào theo thời gian ---------- */
  function capNhatLoiChao() {
    const bayGio = new Date();
    const gio = bayGio.getHours();
    let text;

    if (gio >= 5 && gio < 11) text = "🌅 Chào buổi sáng!ヾ(> ▽ <)ゝ";
    else if (gio >= 11 && gio < 13) text = "☀️ Chào buổi trưa! ╰(*▽*)╯";
    else if (gio >= 13 && gio < 18) text = "🌤️ Chào buổi chiều! o( •̀ ω •́ )✧";
    else if (gio >= 18 && gio < 22) text = "🌆 Chào buổi tối! ♪(^∇^*)";
    else text = "🌙 Khuya rồi, nghỉ ngơi sớm nhé! (σ｀д′)σ";

    if (loiChao) {
      loiChao.textContent = text;
    }
    if (dongHo) {
      const hh = String(gio).padStart(2, "0");
      const mm = String(bayGio.getMinutes()).padStart(2, "0");
      dongHo.textContent = "Bây giờ: " + hh + ":" + mm;
    }
  }
  capNhatLoiChao();
  setInterval(capNhatLoiChao, 30 * 1000); // cập nhật mỗi 30 giây

  /* ---------- Chuyển giao diện sáng/tối & Ghi nhớ bằng Cookie ---------- */

  // Hàm phụ trợ đọc giá trị Cookie
  function layCookie(name) {
    const match = document.cookie.match(
      new RegExp("(^| )" + name + "=([^;]+)"),
    );
    return match ? match[2] : null;
  }

  // Hàm phụ trợ ghi Cookie (lưu trong 30 ngày)
  function ghiCookie(name, value, days = 30) {
    const d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};path=/;expires=${d.toUTCString()}`;
  }

  function apDungGiaoDien(toi) {
    document.body.classList.toggle("dark-mode", toi);
    if (nut) {
      nut.textContent = toi
        ? "☀️ Chuyển sang giao diện sáng (>∇<)ﾉ"
        : "🌙 Chuyển sang giao diện tối (￣o￣) . z Z";
      nut.setAttribute("aria-pressed", String(toi));
    }
  }

  // Ưu tiên 1: Đọc từ Cookie (đã được PHP khởi tạo/đồng bộ)
  let toi = false;
  const cookieTheme = layCookie("theme");

  if (cookieTheme) {
    toi = cookieTheme === "dark";
  } else {
    // Ưu tiên 2: Lấy theo cấu hình hệ điều hành nếu chưa có Cookie
    if (window.matchMedia) {
      toi = window.matchMedia("(prefers-color-scheme: dark)").matches;
    }
  }

  // Áp dụng trạng thái ban đầu
  apDungGiaoDien(toi);

  // Lắng nghe sự kiện click nút bấm đổi giao diện và ghi nhớ Cookie
  if (nut) {
    nut.addEventListener("click", () => {
      toi = !document.body.classList.contains("dark-mode");

      // Ghi nhớ trạng thái vào Cookie để PHP ở server đọc được
      ghiCookie("theme", toi ? "dark" : "light");

      // Tải lại trang ngay lập tức giúp PHP render đúng trạng thái cookie vừa lưu
      location.reload();
    });
  }
});
