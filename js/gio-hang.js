document.addEventListener('DOMContentLoaded', () => {
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
                form.submit(); // Chuyển về submit form thuần nếu JS lỗi
            });
        });
    });
});