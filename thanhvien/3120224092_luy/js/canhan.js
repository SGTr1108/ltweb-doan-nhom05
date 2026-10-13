/*
Tệp js/canhan.js - Code tương tác cho trang cá nhân Lũy (Phần C)
Chức năng 1 (Typing Effect): Hiệu ứng gõ chữ tự động cho phần giới thiệu.
Chức năng 2 (Accordion): Click tiêu đề "Định hướng và Sở thích" để thu gọn/mở rộng. 
Thử nghiệm: Dùng phím Tab để focus vào tiêu đề và nhấn Enter/Space để đóng mở.
*/

document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // CHỨC NĂNG 1: HIỆU ỨNG GÕ CHỮ (TYPING EFFECT)
    // ==========================================
    const typingElement = document.getElementById('typing-text');
    if (typingElement) {
        // Lấy đoạn văn bản từ thuộc tính data-text
        const textToType = typingElement.getAttribute('data-text');
        typingElement.textContent = ''; // Xóa chữ tĩnh trên HTML
        typingElement.classList.add('typing-cursor'); // Bật con trỏ nhấp nháy

        let i = 0;
        const typingSpeed = 40; // Tốc độ gõ (ms)

        function typeWriter() {
            if (i < textToType.length) {
                // Dùng textContent nối thêm từng chữ, an toàn chống XSS
                typingElement.textContent += textToType.charAt(i);
                i++;
                setTimeout(typeWriter, typingSpeed);
            } else {
                // Gõ xong thì xóa class con trỏ nhấp nháy
                typingElement.classList.remove('typing-cursor');
            }
        }
        
        // Trì hoãn 500ms trước khi bắt đầu gõ để người dùng kịp nhìn
        setTimeout(typeWriter, 500);
    }

    // ==========================================
    // CHỨC NĂNG 2: THU GỌN / MỞ RỘNG (ACCORDION)
    // ==========================================
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            // Lấy ID của thẻ nội dung được nhúng trong aria-controls
            const contentId = header.getAttribute('aria-controls');
            const content = document.getElementById(contentId);

            if (content) {
                const isOpen = content.classList.contains('open');

                if (isOpen) {
                    // Nếu đang mở thì đóng lại
                    content.classList.remove('open');
                    header.setAttribute('aria-expanded', 'false'); // Hỗ trợ trình đọc màn hình
                } else {
                    // Nếu đang đóng thì mở ra
                    content.classList.add('open');
                    header.setAttribute('aria-expanded', 'true');
                }
            }
        });
    });
});