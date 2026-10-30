
document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. THÊM SẢN PHẨM VÀO GIỎ HÀNG
    // ==========================================

    const formsThemGio = document.querySelectorAll('.form-them-gio-hang');
    const badgeSoMonHeader = document.getElementById('so-luong-gio-hang-header');

    formsThemGio.forEach(form => {

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const idSp = form.querySelector('input[name="id"]')?.value;
            const soLuong = form.querySelector('input[name="so_luong"]')?.value || 1;

            fetch('api/gio-hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: parseInt(idSp, 10),
                    so_luong: parseInt(soLuong, 10)
                })
            })
            .then(res => res.json())
            .then(data => {

                if (data.loi) {
                    alert(data.loi);
                } else {
                    alert(data.thong_bao);

                    if (badgeSoMonHeader) {
                        badgeSoMonHeader.textContent = data.tong_so_luong;
                    }
                }

            })
            .catch(err => {
                console.error('Lỗi khi thêm giỏ hàng:', err);
                form.submit();
            });

        });

    });


    // ==========================================
    // 2. TĂNG / GIẢM SỐ LƯỢNG
    // ==========================================

    document.addEventListener('click', (event) => {

        const button = event.target.closest(
            '.giam-so-luong, .tang-so-luong'
        );

        if (!button) return;

        const form = button.closest('.gio-hang-quantity-form-v2');

        if (!form || form.dataset.submitting === '1') return;

        const input = form.querySelector('input[name="so_luong"]');

        if (!input) return;

        const min = Number(input.min || 1);
        const max = Number(input.max || Number.MAX_SAFE_INTEGER);
        const current = Number(input.value || min);

        const next = button.classList.contains('tang-so-luong')
            ? Math.min(current + 1, max)
            : Math.max(current - 1, min);

        if (next === current) return;

        input.value = String(next);

        // Đánh dấu để tránh gửi form nhiều lần.
        form.dataset.submitting = '1';

        // Khóa nút trong lúc gửi yêu cầu.
        form.querySelectorAll('button').forEach(item => {
            item.disabled = true;
        });

        // Tự động cập nhật, không cần nút Cập nhật.
        form.submit();

    });


    // ==========================================
    // 3. TỰ ĐỘNG CẬP NHẬT KHI NHẬP TAY
    // ==========================================

    document.addEventListener('change', (event) => {

        const input = event.target;

        if (
            !(input instanceof HTMLInputElement) ||
            input.name !== 'so_luong'
        ) {
            return;
        }

        const form = input.closest('.gio-hang-quantity-form-v2');

        if (!form || form.dataset.submitting === '1') return;

        const min = Number(input.min || 1);
        const max = Number(input.max || Number.MAX_SAFE_INTEGER);
        const value = Number(input.value);

        // Kiểm tra số lượng hợp lệ.
        if (
            !Number.isInteger(value) ||
            value < min ||
            value > max
        ) {
            input.reportValidity();
            return;
        }

        form.dataset.submitting = '1';

        form.querySelectorAll('button').forEach(button => {
            button.disabled = true;
        });

        form.submit();

    });


    // ==========================================
    // 4. CẬP NHẬT TRẠNG THÁI NÚT + VÀ -
    // ==========================================

    document.addEventListener('input', (event) => {

        const input = event.target;

        if (
            !(input instanceof HTMLInputElement) ||
            input.name !== 'so_luong'
        ) {
            return;
        }

        const form = input.closest('.gio-hang-quantity-form-v2');

        if (!form) return;

        const min = Number(input.min || 1);
        const max = Number(input.max || Number.MAX_SAFE_INTEGER);
        const value = Number(input.value);

        const decrease = form.querySelector('.giam-so-luong');
        const increase = form.querySelector('.tang-so-luong');

        if (decrease) {
            decrease.disabled =
                !Number.isFinite(value) || value <= min;
        }

        if (increase) {
            increase.disabled =
                !Number.isFinite(value) || value >= max;
        }

    });

});
