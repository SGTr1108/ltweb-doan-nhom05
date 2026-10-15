<?php
/*
 * Tệp: gioithieu.php
 * Sinh viên: Trần Xuân Sang (MSSV: 3120224122)
 */

session_start();
$goc = '../../';

// Nạp cấu hình, hàm chung và header của nhóm
require $goc . 'inc/config.php';
require_once $goc . 'inc/ham.php';
require $goc . 'inc/header.php';

// Dự phòng trường hợp file inc/ham.php chưa định nghĩa hàm e()
if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// --- CHỨC NĂNG 1: Mảng danh sách dự án ---
$du_an = [
    [
        'ten' => 'Thùng rác thông minh AI',
        'cong_nghe' => 'Arduino, ESP8266, Python, YOLO',
        'mo_ta' => 'Tự động phân loại rác qua nhận diện hình ảnh và gửi cảnh báo qua ứng dụng Blynk IoT.'
    ],
    [
        'ten' => 'Ứng dụng Chat TCP/UDP',
        'cong_nghe' => 'Python, Socket, Multi-threading',
        'mo_ta' => 'Xây dựng máy chủ và máy khách giao tiếp mạng đa luồng, hỗ trợ broadcast tin nhắn.'
    ],
    [
        'ten' => 'Giải quyết Extended Knapsack',
        'cong_nghe' => 'C++, Giải thuật PSO & Tham lam',
        'mo_ta' => 'Tối ưu hóa bài toán cái túi mở rộng bằng thuật toán bầy đàn (Particle Swarm Optimization).'
    ]
];

// --- CHỨC NĂNG 2: Sổ lưu bút (JSONL) ---
$thu_muc_storage = $goc . 'storage/';
$file_luu_but = $thu_muc_storage . '3120224122_luubut.jsonl';
$loi = '';

if (!is_dir($thu_muc_storage)) {
    mkdir($thu_muc_storage, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? '');
    $loi_nhan = trim($_POST['loi_nhan'] ?? '');

    if ($ten !== '' && $loi_nhan !== '') {
        $data = json_encode([
            'ten' => $ten,
            'loi_nhan' => $loi_nhan,
            'thoi_gian' => date('d/m/Y H:i:s')
        ]) . PHP_EOL;

        file_put_contents($file_luu_but, $data, FILE_APPEND | LOCK_EX);
        header("Location: gioithieu.php?status=success");
        exit;
    } else {
        $loi = 'Vui lòng nhập đầy đủ tên và lời nhắn.';
    }
}

$danh_sach_luu_but = [];
if (file_exists($file_luu_but)) {
    $lines = file($file_luu_but, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines);
    $lines = array_slice($lines, 0, 5);
    foreach ($lines as $line) {
        $danh_sach_luu_but[] = json_decode($line, true);
    }
}
?>

<!-- ================= PHẦN HTML CỦA BẠN ================= -->
<link rel="stylesheet" href="style_canhan.css">

<main class="profile-container">
    <div class="page-title-banner">
        <h1>Trang giới thiệu cá nhân</h1>
    </div>

    <nav>
        <a href="../../index.php">Quay về Trang chủ Nhóm</a>
        <a href="../../gioi-thieu.php">Quay lại Giới thiệu Nhóm</a>
    </nav>

    <!-- Section: Giới thiệu bản thân và ảnh chân dung -->
    <section class="profile-header">
        <div class="profile-avatar">
            <img src="Sang-avatar.png" alt="Ảnh chân dung sinh viên Trần Xuân Sang" class="profile-img">
        </div>

        <div class="profile-info">
            <h2>Thông tin sinh viên</h2>
            <p><strong>Họ và tên:</strong> Trần Xuân Sang</p>
            <p><strong>Mã sinh viên:</strong> 3120224122</p>
            <p><strong>Lớp học phần:</strong> Thiết kế và Lập trình Web - Nhóm 05</p>
            <p>Chào mừng bạn đến với trang cá nhân của mình trong dự án phát triển hệ thống quản lý bán hàng MiniPos.</p>

            <h3>Kỹ năng chuyên môn</h3>
            <ul class="skills-list">
                <li>Ngôn ngữ: C++, Python, PHP</li>
                <li>Công cụ: Visual Studio Code, Git/GitHub</li>
                <li>Cơ sở dữ liệu: MySQL, PostgreSQL</li>
            </ul>
        </div>
    </section>

    <!-- Section: Dự án thực tế (Render từ mảng PHP) -->
    <section class="tabs-section">
        <h2>Dự án chuyên môn</h2>
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;">
            <?php foreach ($du_an as $da): ?>
                <div style="flex: 1; min-width: 250px; border: 1px solid #ccc; padding: 15px; border-radius: 8px; background: #fff;">
                    <h3 style="color: #2b5c92; margin-top: 0;"><?= e($da['ten']) ?></h3>
                    <p><strong>Công nghệ:</strong> <?= e($da['cong_nghe']) ?></p>
                    <p><?= e($da['mo_ta']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section: Sổ lưu bút PHP -->
    <section class="bmi-section">
        <h2>Sổ lưu bút</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <p style="color: green; font-weight: bold;">Cảm ơn bạn đã để lại lời nhắn!</p>
        <?php endif; ?>

        <?php if ($loi !== ''): ?>
            <p style="color: red; font-weight: bold;"><?= e($loi) ?></p>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;">
            <!-- Form Gửi -->
            <div style="flex: 1; min-width: 300px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e1e8ed;">
                <form method="POST" action="gioithieu.php" class="bmi-form">
                    <label for="ten">Tên của bạn:</label>
                    <input type="text" id="ten" name="ten" required>

                    <label for="loi_nhan">Lời nhắn:</label>
                    <textarea id="loi_nhan" name="loi_nhan" rows="4" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required></textarea>

                    <button type="submit">Gửi lời nhắn</button>
                </form>
            </div>

            <!-- Danh sách -->
            <div style="flex: 1; min-width: 300px;">
                <h3 style="margin-top:0;">5 Lời nhắn mới nhất</h3>
                <?php if (empty($danh_sach_luu_but)): ?>
                    <p style="color: #777;">Chưa có lời nhắn nào.</p>
                <?php else: ?>
                    <?php foreach ($danh_sach_luu_but as $lb): ?>
                        <div style="background: #fff; border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                            <strong style="color: #2b5c92;"><?= e($lb['ten']) ?></strong>
                            <small style="color: #888; float: right;"><?= e($lb['thoi_gian']) ?></small>
                            <p style="margin: 5px 0 0 0;"><?= e($lb['loi_nhan']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Bảng: Thời khóa biểu tuần (Giữ nguyên) -->
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

<script type="module" src="js/canhan.js"></script>

<?php require $goc . 'inc/footer.php'; ?>