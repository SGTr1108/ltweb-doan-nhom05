document.addEventListener('DOMContentLoaded', () => {
    const oTimKiem = document.getElementById('tim-kiem');
    const khungKetQua = document.querySelector('.luoi-san-pham');
    const trangThai = document.getElementById('trang-thai-san-pham');

    if (!oTimKiem || !khungKetQua) return;

    let timeoutId = null;

    oTimKiem.addEventListener('input', (e) => {
        clearTimeout(timeoutId);
        const tuKhoa = e.target.value.trim();

        // Chờ 300ms sau khi ngừng gõ
        timeoutId = setTimeout(() => {
            fetch(`api/san-pham.php?q=${encodeURIComponent(tuKhoa)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Lỗi truy vấn API');
                    return res.json();
                })
                .then(data => {
                    if (trangThai) {
                        trangThai.textContent = `Có ${data.length} sản phẩm phù hợp.`;
                    }

                    if (data.length === 0) {
                        khungKetQua.innerHTML = '<p>Không tìm thấy sản phẩm phù hợp.</p>';
                        return;
                    }

                    khungKetQua.innerHTML = data.map(sp => `
                        <article class="the-san-pham">
                            <img src="${sp.anh || 'images/MiniPOS.webp'}" alt="${sp.ten}" width="800" height="533">
                            <div class="noi-dung-san-pham">
                                <h3>${sp.ten}</h3>
                                <p class="ma-san-pham">Mã sản phẩm: ${sp.id}</p>
                                <p class="gia-san-pham">${new Intl.NumberFormat('vi-VN').format(sp.gia)} đ</p>
                                <p>Tồn kho: ${sp.tonKho}</p>
                                <div class="hang-nut-san-pham">
                                    <a href="chi-tiet.php?id=${sp.id}">Xem chi tiết</a>
                                </div>
                            </div>
                        </article>
                    `).join('');
                })
                .catch(err => console.error(err));
        }, 300);
    });
});