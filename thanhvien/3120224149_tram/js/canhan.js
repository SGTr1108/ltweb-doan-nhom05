/**
 * TẬP TIN: js/canhan.js
 * CHỨC NĂNG: Điều khiển hiệu ứng cuộn mượt lên đầu trang và trình xem ảnh phóng to (Lightbox).
 * CÁCH THỬ: 
 *   1. Cuộn trang xuống dưới -> Xuất hiện nút mũi tên góc phải -> Bấm để cuộn mượt lên đỉnh.
 *   2. Nhấp chuột vào ảnh chân dung đại diện -> Khung nền đen hiện ra hiển thị ảnh phóng to kèm chú thích.
 *   3. Bấm vào nút dấu (X) góc trên hoặc bấm vào vùng nền đen bên ngoài để đóng trình phóng to ảnh.
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // =========================================================================
  // 1. CHỨC NĂNG: NÚT CUỘN LÊN ĐẦU TRANG (BACK TO TOP)
  // =========================================================================
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (scrollToTopBtn) {
    // Ẩn/hiện nút dựa trên khoảng cách cuộn của trình duyệt
    window.addEventListener("scroll", function () {
      if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        scrollToTopBtn.classList.add("active");
      } else {
        scrollToTopBtn.classList.remove("active");
      }
    });

    // Xử lý cuộn mượt khi click vào nút bằng addEventListener
    scrollToTopBtn.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }

  // =========================================================================
  // 2. CHỨC NĂNG: TRÌNH PHÓNG TO ẢNH CHÂN DUNG (LIGHTBOX VIA DOM METHODS)
  // =========================================================================
  const profileImg = document.querySelector(".profile-img");
  const lightboxContainer = document.getElementById("imageLightbox");

  // Kiểm tra điều kiện tồn tại để ngăn chặn triệt để lỗi "Uncaught TypeError" trong Console
  if (profileImg && lightboxContainer) {
    
    // Tạo phần tử nút Đóng (X) bằng createElement
    const closeBtn = document.createElement("span");
    closeBtn.classList.add("lightbox-close");
    closeBtn.textContent = "×"; // Sử dụng textContent thay vì innerHTML để bảo mật dữ liệu

    // Tạo phần tử thẻ hiển thị hình ảnh phóng to bằng createElement
    const targetImg = document.createElement("img");
    targetImg.classList.add("lightbox-content");
    targetImg.alt = "Ảnh chân dung phóng to sinh viên";

    // Đưa các phần tử vừa tạo lồng vào trong khung nền tối của ứng dụng
    lightboxContainer.appendChild(closeBtn);
    lightboxContainer.appendChild(targetImg);

    // Sự kiện 2A: Nhấp chuột vào ảnh đại diện gốc để hiển thị Lightbox phóng to
    profileImg.addEventListener("click", function () {
      targetImg.src = profileImg.src; // Sao chép đường dẫn ảnh tự động
      lightboxContainer.classList.add("open"); // Hiển thị khung qua classList
    });

    // Sự kiện 2B: Nhấp chuột vào nút (X) để ẩn khung phóng to
    closeBtn.addEventListener("click", function () {
      lightboxContainer.classList.remove("open");
    });

    // Sự kiện 2C: Nhấp chuột ra ngoài vùng ảnh (vùng nền đen) để ẩn khung phóng to nhanh
    lightboxContainer.addEventListener("click", function (event) {
      if (event.target === lightboxContainer) {
        lightboxContainer.classList.remove("open");
      }
    });
  }
});
