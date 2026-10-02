document.addEventListener("DOMContentLoaded", function() {

  // ==========================================
  // 1. XỬ LÝ NÚT CUỘN LÊN ĐẦU TRANG
  // ==========================================
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (scrollToTopBtn) {
    window.onscroll = function() {
      if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        scrollToTopBtn.style.display = "block";
      } else {
        scrollToTopBtn.style.display = "none";
      }
    };

    scrollToTopBtn.addEventListener("click", function() {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  }

  // ==========================================
  // 2. XỬ LÝ PHÓNG TO ẢNH CHÂN DUNG (LIGHTBOX)
  // ==========================================
  const profileImg = document.querySelector(".profile-img");
  const lightbox = document.getElementById("imageLightbox");
  const lightboxImg = document.getElementById("lightboxImg");
  const lightboxClose = document.querySelector(".lightbox-close");

  if (profileImg && lightbox && lightboxImg && lightboxClose) {
    // Khi click vào ảnh đại diện -> Mở khung phóng to
    profileImg.addEventListener("click", function() {
      lightbox.style.display = "block";
      lightboxImg.src = this.src; // Lấy nguồn ảnh từ avatar gốc gán qua
    });

    // Khi click vào dấu X -> Đóng khung phóng to
    lightboxClose.addEventListener("click", function() {
      lightbox.style.display = "none";
    });

    // Khi click vào vùng nền đen bên ngoài ảnh -> Cũng tự động đóng khung
    lightbox.addEventListener("click", function(event) {
      if (event.target === lightbox) {
        lightbox.style.display = "none";
      }
    });
  }

});
