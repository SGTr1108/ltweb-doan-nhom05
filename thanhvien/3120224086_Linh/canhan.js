/* canhan.js - Tương tác trang cá nhân: lời chào theo giờ, đồng hồ và nút đổi giao diện sáng/tối.
 * Dùng addEventListener, cập nhật trang bằng textContent / classList / createElement.
 * Cách thử: mở gioithieu.html, xem lời chào và giờ hiện ở đầu phần "Thông tin sinh viên".
 * Bấm nút "Chuyển sang giao diện tối" để đổi giao diện, tải lại trang (F5) thì vẫn giữ lựa chọn.
 * Mở F12 → Console: không có dòng lỗi "Uncaught" nào. */

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
    khuTuongTac.appendChild(dongHo);
  }

  /* ---------- Lời chào theo thời gian ---------- */
  function capNhatLoiChao() {
    const bayGio = new Date();
    const gio = bayGio.getHours();
    let text;

    if (gio >= 5 && gio < 11) text = "🌅 Chào buổi sáng!ヾ(≧▽≦*)";
    else if (gio >= 11 && gio < 13) text = "☀️ Chào buổi trưa! ╰(*▽*)╯";
    else if (gio >= 13 && gio < 18) text = "🌤️ Chào buổi chiều! o( •̀ ω •́ )✧";
    else if (gio >= 18 && gio < 22) text = "🌆 Chào buổi tối! ♪(^∇^*)";
    else text = "🌙 Khuya rồi, nghỉ ngơi sớm nhé!";

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

  /* ---------- Chuyển giao diện sáng/tối ---------- */
  function apDungGiaoDien(toi) {
    document.body.classList.toggle("dark-mode", toi);
    if (nut) {
      nut.textContent = toi
        ? "☀️ Chuyển sang giao diện sáng (≧∇≦)ﾉ"
        : "🌙 Chuyển sang giao diện tối (￣o￣) . z Z";
      nut.setAttribute("aria-pressed", String(toi));
    }
  }

  // Lấy lựa chọn đã lưu; nếu chưa có thì theo cài đặt của hệ điều hành
  let toi = false;
  if (window.matchMedia) {
    toi = window.matchMedia("(prefers-color-scheme: dark)").matches;
  }
  try {
    const daLuu = localStorage.getItem("giao-dien");
    if (daLuu) toi = daLuu === "toi";
  } catch (e) {
    /* localStorage bị chặn: dùng giá trị mặc định */
  }
  apDungGiaoDien(toi);

  if (nut) {
    nut.addEventListener("click", () => {
      toi = !document.body.classList.contains("dark-mode");
      apDungGiaoDien(toi);
      try {
        localStorage.setItem("giao-dien", toi ? "toi" : "sang");
      } catch (e) {
        /* bỏ qua nếu không lưu được */
      }
    });
  }
});
