<?php
/*
 * Tệp: gioithieu.php (Sinh viên: Nguyễn Võ Khánh Linh - 3120224086)
 * Chức năng: Hiển thị thông tin cá nhân, giao diện sáng/tối ghi nhớ bằng Cookie
 *            và nhật ký truy cập ghi vào thư mục storage của dự án.
 * Cách thử: Mở trang qua localhost, tải lại vài lần rồi xem mục nhật ký truy cập.
 *           Bấm nút chuyển giao diện để kiểm tra cookie. Kiểm tra tệp nhật ký
 *           được sinh ra trong thư mục storage (không phải cạnh tệp trang).
 */

// Xác định đường dẫn gốc dự án so với thư mục hiện tại
$goc = '../../';
$tieuDeTrang = 'Giới thiệu cá nhân - Linh';
$cssTrang = 'profile.css';

// Nạp tệp cấu hình của nhóm. config.php đã tự nạp ham.php (có hàm e()) nên không cần nạp lại.
require_once $goc . 'inc/config.php';

// --- CHỨC NĂNG 1: Nhật ký truy cập cá nhân ---
// Dùng đường dẫn tuyệt đối theo __DIR__ để chạy từ thư mục nào cũng ghi đúng chỗ.
$thu_muc_storage = __DIR__ . '/../../storage/';
$file_nhat_ky = $thu_muc_storage . '3120224086_nhatky.txt';

if (!is_dir($thu_muc_storage)) {
    mkdir($thu_muc_storage, 0777, true);
}

$thoi_gian = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Không rõ';
$trinh_duyet = $_SERVER['HTTP_USER_AGENT'] ?? 'Không rõ';
$dong_nhat_ky = "[$thoi_gian] - IP: $ip - Trình duyệt: $trinh_duyet" . PHP_EOL;

file_put_contents($file_nhat_ky, $dong_nhat_ky, FILE_APPEND | LOCK_EX);

// Đọc lại nhật ký để hiển thị (tối đa 5 lượt truy cập gần nhất)
$logs = [];
if (file_exists($file_nhat_ky)) {
    $tat_ca = file($file_nhat_ky, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($tat_ca !== false) {
        $logs = array_slice(array_reverse($tat_ca), 0, 5);
    }
}

// --- CHỨC NĂNG 2: Giao diện Sáng / Tối qua Cookie ---
$currentTheme = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : 'light';

// Gọi header dùng chung của nhóm
require_once $goc . 'inc/header.php';
?>

<?php if ($currentTheme === 'dark'): ?>
    <!-- header.php chưa hỗ trợ class cho <body>, nên gán dark-mode ngay khi trang nạp -->
    <script>document.body.classList.add('dark-mode');</script>
<?php endif; ?>

<!-- ================= PHẦN HTML CỦA BẠN ================= -->
<main class="profile-container">
    <div class="page-title-banner">
        <h1>Trang giới thiệu cá nhân</h1>
    </div>

    <nav>
        <a href="<?= e($goc) ?>index.php">Quay về Trang chủ Nhóm</a>
        <a href="<?= e($goc) ?>gioi-thieu.php">Quay lại Giới thiệu Nhóm</a>
    </nav>

    <!-- Section: Giới thiệu bản thân và ảnh chân dung -->
    <section class="profile-header">
        <!-- Cột 1: Ảnh chân dung -->
        <div class="profile-avatar">
            <img
                src="linh_avatar.png"
                alt="Ảnh chân dung sinh viên Nguyễn Võ Khánh Linh"
                class="profile-img">
        </div>

        <!-- Cột 2: Nội dung giới thiệu -->
        <div class="profile-info">
            <h2>Thông tin sinh viên</h2>
            <div class="tuong-tac-ca-nhan">
                <p id="loi-chao" class="loi-chao" aria-live="polite">
                    👋 Xin chào!
                </p>
                <button id="nut-giao-dien" type="button" aria-pressed="<?= $currentTheme === 'dark' ? 'true' : 'false' ?>">
                    <?= $currentTheme === 'dark' ? '☀️ Chuyển sang giao diện sáng (>∇<)ﾉ' : '🌙 Chuyển sang giao diện tối (￣o￣) . z Z' ?>
                </button>
            </div>
            <p><strong>Họ và tên:</strong> Nguyễn Võ Khánh Linh</p>
            <p><strong>Mã sinh viên:</strong> 3120224086</p>
            <p>
                <strong>Lớp học phần:</strong> Thiết kế và Lập trình Web - Nhóm 05
            </p>
            <p>
                Chào mừng bạn đến với trang cá nhân của mình trong dự án phát triển
                hệ thống quản lý bán hàng MiniPos.
            </p>

            <h3>Kỹ năng chuyên môn</h3>
            <ul class="skills-list">
                <li>Ngôn ngữ: C++, Python</li>
                <li>Công cụ: Visual Studio Code, Git/GitHub</li>
                <li>Cơ sở dữ liệu: MySQL</li>
            </ul>
        </div>
    </section>

    <!-- Article: Dự án và Sở thích -->
    <article>
        <h2>Định hướng và Sở thích</h2>
        <p>
            Trong đồ án nhóm MiniPos, mình đảm nhận việc thiết kế cấu trúc giao
            diện HTML ngữ nghĩa, đảm bảo các tiêu chuẩn về khả năng truy cập
            (A11y) và tối ưu hóa SEO on-page.
        </p>
        <p>
            Sở thích cá nhân của mình là tìm hiểu các mô hình kiến trúc phần mềm 3
            tầng và phát triển các ứng dụng web thực tế phục vụ đời sống.
        </p>
    </article>

    <!-- Section: Lịch sử nhật ký truy cập cá nhân -->
    <section class="access-log-section" style="margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 8px;">
        <h2>📜 Nhật ký truy cập cá nhân gần đây</h2>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $logItem): ?>
                    <li><small><?= e($logItem) ?></small></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Chưa có lịch sử truy cập nào.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- Bảng: Thời khóa biểu tuần -->
    <section>
        <h2>Thời khóa biểu cá nhân</h2>
        <div class="table-responsive">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th scope="col">Tiết</th>
                        <th scope="col">Thứ 2</th>
                        <th scope="col">Thứ 3</th>
                        <th scope="col">Thứ 4</th>
                        <th scope="col">Thứ 5</th>
                        <th scope="col">Thứ 6</th>
                        <th scope="col">Thứ 7</th>
                        <th scope="col">Chủ nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="period">1</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td rowspan="3">
                            31231330 - 24-0101<br>
                            Khai phá dữ liệu<br>
                            Phòng: A5-404B
                        </td>
                        <td rowspan="3">
                            31231398 - 24-0101<br>
                            Lập trình mạng<br>
                            Phòng: A5-403
                        </td>
                        <td rowspan="3">
                            31231755 - 24-0102<br>
                            Thiết kế và lập trình web<br>
                            Phòng: B3-303
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">2</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">3</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">4</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">5</td>
                        <td></td>
                        <td rowspan="2">
                            21221904 - 24-0113<br>
                            Lịch sử Đảng Cộng sản Việt Nam<br>
                            Phòng: A6-403
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">6</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="break-row">
                        <td colspan="8"></td>
                    </tr>
                    <tr>
                        <td class="period">7</td>
                        <td></td>
                        <td rowspan="3">
                            31241283 - 24-0103<br>
                            Hệ quản trị cơ sở dữ liệu<br>
                            Phòng: B3-304
                        </td>
                        <td></td>
                        <td rowspan="3">
                            31231016 - 24-0102<br>
                            Công nghệ phần mềm<br>
                            Phòng: B3-503
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">8</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">9</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">10</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">11</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td rowspan="2">
                            31221010 - 24-0103<br>
                            An toàn thông tin<br>
                            Phòng: B3-506
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="period">12</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="js/canhan.js" defer></script>

<?php require_once $goc . 'inc/footer.php'; ?>