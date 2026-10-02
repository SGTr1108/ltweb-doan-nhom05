/*
Tệp js/canhan.js - Code tương tác cho trang cá nhân (Phần C)
Chức năng 1 (Tabbed Content): Chuyển đổi qua lại giữa các nội dung. Thử nghiệm: Click chuột hoặc dùng phím Tab di chuyển tới nút và nhấn Enter.
Chức năng 2 (Tính BMI): Nhập số liệu và tính kết quả. Thử nghiệm: Nhập chiều cao, cân nặng và bấm "Tính BMI". Kết quả hiển thị tức thời qua textContent.
*/

document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // CHỨC NĂNG 1: CHUYỂN ĐỔI TAB (TABBED CONTENT)
    // ==========================================
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // 1. Xóa trạng thái active của tất cả các tab
            tabBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            tabContents.forEach(c => c.classList.remove('active'));

            // 2. Kích hoạt tab hiện tại vừa bấm
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
            
            // 3. Hiển thị nội dung tương ứng dựa vào thuộc tính aria-controls
            const targetId = btn.getAttribute('aria-controls');
            document.getElementById(targetId).classList.add('active');
        });
    });

    // ==========================================
    // CHỨC NĂNG 2: TÍNH CHỈ SỐ BMI
    // ==========================================
    const btnTinhBMI = document.getElementById('btn-tinh-bmi');
    const inputChieuCao = document.getElementById('chieu-cao');
    const inputCanNang = document.getElementById('can-nang');
    const kqBMI = document.getElementById('kq-bmi');

    if (btnTinhBMI) {
        btnTinhBMI.addEventListener('click', () => {
            // Lấy giá trị và chuyển thành số thập phân
            const heightCm = parseFloat(inputChieuCao.value);
            const weightKg = parseFloat(inputCanNang.value);

            // Kiểm tra dữ liệu đầu vào hợp lệ không
            if (!heightCm || !weightKg || heightCm <= 0 || weightKg <= 0) {
                kqBMI.textContent = 'Vui lòng nhập chiều cao và cân nặng hợp lệ bằng số!';
                kqBMI.style.color = '#d9534f'; // Màu đỏ báo lỗi
                return;
            }

            // Công thức BMI = Cân nặng (kg) / Chiều cao(m)^2
            const heightM = heightCm / 100;
            const bmi = (weightKg / (heightM * heightM)).toFixed(1);
            
            // Xếp loại BMI
            let danhGia = '';
            if (bmi < 18.5) {
                danhGia = 'Thiếu cân';
            } else if (bmi >= 18.5 && bmi <= 24.9) {
                danhGia = 'Bình thường - Chúc mừng bạn có vóc dáng tốt!';
            } else if (bmi >= 25 && bmi <= 29.9) {
                danhGia = 'Thừa cân';
            } else {
                danhGia = 'Béo phì';
            }

            // Gắn kết quả ra HTML (Dùng textContent để chống XSS theo đúng yêu cầu đề bài)
            kqBMI.textContent = `Chỉ số BMI của bạn là ${bmi} (${danhGia}).`;
            kqBMI.style.color = '#2b5c92'; // Màu xanh biển thành công
        });
    }
});