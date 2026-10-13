/**
 * TẬP TIN: js/canhan.js
 * CHỨC NĂNG: Điều khiển nút cuộn mượt lên đầu trang và trình xem ảnh phóng to (Lightbox).
 * CÁCH THỬ: 
 * 1. Cuộn trang xuống hơn 300px để kiểm tra nút "Lên đầu trang", sau đó nhấn nút để cuộn lên đầu.
 * 2. LIGHTBOX: Nhấn vào ảnh chân dung để phóng to, dùng nút X hoặc nhấn ESC để đóng ảnh.
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // =========================================================================
  // 1. CHỨC NĂNG: NÚT CUỘN LÊN ĐẦU TRANG (BACK TO TOP)
  // =========================================================================
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (scrollToTopBtn) {
    window.addEventListener("scroll", function () {
      if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        scrollToTopBtn.classList.add("active");
      } else {
        scrollToTopBtn.classList.remove("active");
      }
    });

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
  const profileImgBtn = document.querySelector(".profile-img-btn");
  const profileImg = document.querySelector(".profile-img");
  const lightboxContainer = document.getElementById("imageLightbox");

  if (profileImgBtn && profileImg && lightboxContainer) {
    
    // Tìm nút đóng có sẵn trong HTML hoặc chỉnh sửa lại thuộc tính của nó
    const closeBtn = lightboxContainer.querySelector(".lightbox-close");

    // Tạo phần tử thẻ hiển thị hình ảnh phóng to bằng createElement
    const targetImg = document.createElement("img");
    targetImg.classList.add("lightbox-content");
    targetImg.alt = "Ảnh chân dung phóng to sinh viên";

    // Đưa thẻ ảnh vừa tạo lồng vào trong khung nền tối
    lightboxContainer.appendChild(targetImg);

    // Hàm mở Lightbox
    function openLightbox() {
      targetImg.src = profileImg.src; 
      lightboxContainer.classList.add("open");
      lightboxContainer.setAttribute("aria-hidden", "false");
      closeBtn.focus(); // Tự động di chuyển con trỏ bàn phím vào nút đóng để tiện thao tác
    }

    // Hàm đóng Lightbox
    function closeLightbox() {
      lightboxContainer.classList.remove("open");
      lightboxContainer.setAttribute("aria-hidden", "true");
      profileImgBtn.focus(); // Trả lại tiêu điểm bàn phím về nút bấm ảnh ban đầu
    }

    // Sự kiện 2A: Nhấp chuột hoặc nhấn Enter/Space vào nút bọc ảnh để hiển thị Lightbox
    profileImgBtn.addEventListener("click", openLightbox);

    // Sự kiện 2B: Nhấp chuột vào nút đóng (X) để ẩn khung phóng to
    if (closeBtn) {
      closeBtn.addEventListener("click", closeLightbox);
    }

    // Sự kiện 2C: Nhấp chuột ra ngoài vùng ảnh (vùng nền đen) để ẩn khung phóng to nhanh
    lightboxContainer.addEventListener("click", function (event) {
      if (event.target === lightboxContainer) {
        closeLightbox();
      }
    });

    // Sự kiện 2D: Hỗ trợ phím ESC (Escape) để đóng trình xem ảnh nhanh
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && lightboxContainer.classList.contains("open")) {
        closeLightbox();
      }
    });
  }
});
