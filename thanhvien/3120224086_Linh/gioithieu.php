<?php
/*
 * Tệp: gioithieu.php (Sinh viên: Nguyễn Võ Khánh Linh - 3120224086)
* Chức năng: Hiển thị thông tin cá nhân, giao diện sáng/tối ghi nhớ bằng Cookie,
 *            nhật ký truy cập và thống kê lượt xem bằng MySQL.
 * Cách thử: Mở trang qua localhost, bấm nút ghi nhận lượt truy cập,
 *           kiểm tra dữ liệu trong hai bảng tv_nhat_ky và tv_luot_xem.
 */

// Xác định đường dẫn gốc dự án so với thư mục hiện tại
$goc = '../../';
$tieuDeTrang = 'Giới thiệu cá nhân - Linh';
$cssTrang = 'profile.css';

// Nạp tệp cấu hình của nhóm. config.php đã tự nạp ham.php (có hàm e()) nên không cần nạp lại.
require_once $goc . 'inc/config.php';
// Nạp chức năng CSRF của nhóm (config.php cần khởi tạo session trước).
require_once $goc . 'inc/csrf.php';

// Thông tin dùng chung cho hai chức năng.
$pdo = db();
$mssv = '3120224086';

// POST: chỉ khi người dùng gửi form hợp lệ mới ghi lượt xem và nhật ký.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrfHopLe()) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ (CSRF). Vui lòng tải lại trang và thử lại.');
    }

    if (($_POST['action'] ?? '') !== 'ghi_nhan_truy_cap') {
        http_response_code(400);
        exit('Thao tác không hợp lệ.');
    }

    // Chức năng 1: ghi nhật ký truy cập vào CSDL tv_nhat_ky.
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Không rõ';
$trinh_duyet = $_SERVER['HTTP_USER_AGENT'] ?? 'Không rõ';

// Lưu nhật ký vào bảng tv_nhat_ky bằng Prepared Statement.
$sql = "INSERT INTO tv_nhat_ky
        (mssv, dia_chi_ip, trinh_duyet)
        VALUES (?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $mssv,
    $ip,
    substr($trinh_duyet, 0, 255)
]);

    // Chức năng 2: tăng lượt xem trong ngày bằng INSERT ... ON DUPLICATE KEY UPDATE.
    $sql = "INSERT INTO tv_luot_xem (mssv, ngay_xem, so_luot)
            VALUES (?, CURDATE(), 1)
            ON DUPLICATE KEY UPDATE so_luot = so_luot + 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mssv]);

    // PRG: chuyển hướng sau POST để tránh gửi lại biểu mẫu khi tải lại trang.
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'), true, 303);
    exit;
}

// GET: chỉ đọc dữ liệu, tuyệt đối không ghi CSDL hoặc tệp.
$sql = "SELECT ngay_xem, so_luot
        FROM tv_luot_xem
        WHERE mssv = ?
          AND ngay_xem >= CURDATE() - INTERVAL 6 DAY
          AND ngay_xem <= CURDATE()
        ORDER BY ngay_xem DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$mssv]);
$luotXem = $stmt->fetchAll();

// Đọc 5 nhật ký truy cập gần nhất từ CSDL.
$sql = "SELECT dia_chi_ip, trinh_duyet, thoi_gian_tao
        FROM tv_nhat_ky
        WHERE mssv = ?
        ORDER BY thoi_gian_tao DESC
        LIMIT 5";

$stmt = $pdo->prepare($sql);
$stmt->execute([$mssv]);
$logs = $stmt->fetchAll();

// --- CHỨC NĂNG 2: Giao diện Sáng / Tối qua Cookie ---
$currentTheme = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : 'light';

// header.php nhận $lopBody để gán class cho <body> (CSP không cho script nội tuyến).
$lopBody = $currentTheme === 'dark' ? 'dark-mode' : '';

// Gọi header dùng chung của nhóm
require_once $goc . 'inc/header.php';
?>

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
    <section class="access-log-section linh-tach-1">
        <h2>📜 Nhật ký truy cập cá nhân gần đây</h2>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="ghi_nhan_truy_cap">
            <button type="submit">Ghi nhận lượt truy cập</button>
        </form>
        <ul class="linh-tach-2">
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $logItem): ?>
    <li>
        <small>
            [<?= e($logItem['thoi_gian_tao']) ?>]
            - IP: <?= e($logItem['dia_chi_ip']) ?>
            - Trình duyệt: <?= e($logItem['trinh_duyet']) ?>
        </small>
    </li>
<?php endforeach; ?>
            <?php else: ?>
                <li>Chưa có lịch sử truy cập nào.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- Chức năng 2: Thống kê lượt xem theo ngày -->
<section class="view-count-section">
    <h2>📊 Thống kê lượt xem trong 7 ngày gần nhất</h2>

    <?php if (!empty($luotXem)): ?>
        <div class="table-responsive">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Ngày truy cập</th>
                        <th>Số lượt xem</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($luotXem as $item): ?>
                        <tr>
                            <td><?= e($item['ngay_xem']) ?></td>
                            <td><?= e((string) $item['so_luot']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Chưa có dữ liệu lượt xem.</p>
    <?php endif; ?>
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