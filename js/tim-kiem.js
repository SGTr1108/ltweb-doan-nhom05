"use strict";

// Tìm kiếm tức thì: gõ/chọn bộ lọc → chờ 300 ms → gọi api/san-pham.php → vẽ lại lưới.
// Bấm "Áp dụng" (hoặc Enter) vẫn gửi form GET bình thường để có phân trang đầy đủ.

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("bo-loc-san-pham");
  const luoi = document.getElementById("luoi-san-pham");
  const trangThai = document.getElementById("trang-thai-san-pham");
  const phanTrang = document.getElementById("phan-trang");

  if (!form || !luoi || !trangThai) return;

  const GIOI_HAN_API = 20; // khớp LIMIT trong api/san-pham.php
  let hen = null;
  let yeuCauCu = null;

  const esc = (giaTri) =>
    String(giaTri ?? "").replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c],
    );

  const vnd = (soTien) => new Intl.NumberFormat("vi-VN").format(soTien) + " ₫";

  const theSanPham = (sp) => `
        <article class="the-san-pham">
            <img src="${esc(sp.anh || "images/MiniPOS.webp")}" alt="Hình ảnh ${esc(sp.ten)}"
                width="800" height="533" loading="lazy"
                data-anh-du-phong="images/MiniPOS.webp">
            <div class="noi-dung-san-pham">
                <h3>${esc(sp.ten)}</h3>
                <p class="ma-san-pham">Mã sản phẩm: ${esc(sp.id)}</p>
                <p class="gia-san-pham">${esc(vnd(sp.gia))}</p>
                <p>Tồn kho: ${esc(sp.tonKho)}</p>
                <div class="hang-nut-san-pham">
                    <a class="nut-xem-chi-tiet" href="chi-tiet.php?id=${encodeURIComponent(sp.id)}">Xem chi tiết</a>
                    <form method="post" action="gio-hang.php" class="form-them-gio-hang">
                        <input type="hidden" name="csrf" value="${esc(luoi.dataset.csrf)}">
                        <input type="hidden" name="hanh_dong" value="them">
                        <input type="hidden" name="id" value="${esc(sp.id)}">
                        <input type="hidden" name="so_luong" value="1">
                        <button type="submit" ${sp.tonKho < 1 ? "disabled" : ""}>Thêm vào giỏ</button>
                    </form>
                </div>
            </div>
        </article>`;

  async function timKiem() {
    const tham = new URLSearchParams();
    const q = form.elements["q"].value.trim();
    const dm = form.elements["danh-muc"].value;
    const gia = form.elements["giaToiDa"].value.trim();

    if (q !== "") tham.set("q", q);
    if (dm !== "") tham.set("dm", dm);
    if (gia !== "") tham.set("giaToiDa", gia);
    tham.set("sx", form.elements["sap-xep"].value);

    // Bỏ yêu cầu cũ để kết quả đến trễ không ghi đè kết quả mới.
    if (yeuCauCu) yeuCauCu.abort();
    yeuCauCu = new AbortController();

    try {
      const res = await fetch("api/san-pham.php?" + tham.toString(), {
        signal: yeuCauCu.signal,
      });
      if (!res.ok) throw new Error("Lỗi truy vấn API: " + res.status);
      const dsSanPham = await res.json();

      if (phanTrang) phanTrang.hidden = true;

      if (dsSanPham.length === 0) {
        luoi.innerHTML =
          "<p>Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>";
        trangThai.textContent = "Có 0 sản phẩm.";
        return;
      }

      luoi.innerHTML = dsSanPham.map(theSanPham).join("");
      trangThai.textContent =
        dsSanPham.length >= GIOI_HAN_API
          ? `Hiển thị ${dsSanPham.length} kết quả đầu tiên. Bấm Áp dụng để xem đủ và phân trang.`
          : `Có ${dsSanPham.length} sản phẩm.`;
    } catch (loi) {
      if (loi.name === "AbortError") return;
      console.error(loi);
      trangThai.textContent =
        "Không tải được kết quả. Hãy thử lại hoặc bấm Áp dụng.";
    }
  }

  function hoanTimKiem() {
    clearTimeout(hen);
    hen = setTimeout(timKiem, 300);
  }

  form.elements["q"].addEventListener("input", hoanTimKiem);
  form.elements["giaToiDa"].addEventListener("input", hoanTimKiem);
  form.elements["danh-muc"].addEventListener("change", hoanTimKiem);
  form.elements["sap-xep"].addEventListener("change", hoanTimKiem);
});
