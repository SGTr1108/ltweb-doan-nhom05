// Đợi cấu trúc HTML của trang tải xong hoàn toàn rồi mới chạy mã bên trong
document.addEventListener("DOMContentLoaded", function() {

  // ==========================================
  // 1. XỬ LÝ NÚT CUỘN LÊN ĐẦU TRANG
  // ==========================================
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (scrollToTopBtn) { // Kiểm tra xem nút có tồn tại không để tránh lỗi null
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

});

// ==========================================
// 2. CẤU HÌNH BỘ DỊCH GOOGLE TRANSLATE ẨN
// ==========================================
// Lưu ý: Hàm này phải để NẰM NGOÀI sự kiện DOMContentLoaded để Google API có thể gọi trực tiếp toàn cục
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'vi',
    includedLanguages: 'en,vi',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  }, 'google_translate_element');
}

// ==========================================
// 3. XỬ LÝ SỰ KIỆN BẤM VÀO LÁ CỜ ĐỂ DỊCH
// ==========================================
function changeLanguage(langCode) {
  const googleSelect = document.querySelector('.goog-te-combo');
  if (googleSelect) {
    googleSelect.value = langCode;
    googleSelect.dispatchEvent(new Event('change'));
  } else {
    alert("Tính năng dịch đang tải hoặc bị trình duyệt chặn trên môi trường local (file://). Bạn hãy chạy bằng Live Server hoặc đẩy lên GitHub Pages nhé!");
  }
}
