<?php
/*
 * Tệp: gioithieu.php
 * Sinh viên: Trần Xuân Sang (MSSV: 3120224122)
 * Chức năng 1: Khởi tạo và in danh sách các dự án thực tế từ mảng PHP.
 * Chức năng 2: Sổ lưu bút (đọc/ghi dữ liệu từ tệp JSONL, kiểm tra POST, chặn XSS và chuyển hướng PRG).
 * Cách thử: Mở http://localhost:8000/thanhvien/3120224122_sang/gioithieu.php, xem danh sách dự án, nhập tên và lời nhắn vào form lưu bút rồi nhấn gửi.
 */

session_start();
$goc = '../../';

// Nạp cấu hình, hàm chung và header của nhóm
require $goc . 'inc/config.php';
require_once $goc . 'inc/ham.php';
require $goc . 'inc/header.php';

// Dự phòng trường hợp file inc/ham.php của nhóm chưa định nghĩa hàm e()
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

// --- CHỨC NĂNG 2: Sổ lưu bút (Xử lý Form PRG) ---
$thu_muc_storage = $goc . 'storage/';
$file_luu_but = $thu_muc_storage . '3120224122_luubut.jsonl';
$loi = '';

// Tạo thư mục storage nếu chưa có (tránh lỗi Warning)
if (!is_dir($thu_muc_storage)) {
    mkdir($thu_muc_storage, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten'] ?? '');
    $loi_nhan = trim($_POST['loi_nhan'] ?? '');

    if ($ten !== '' && $loi_nhan !== '') {
        // Chuẩn bị mảng dữ liệu và mã hóa JSON
        $data = json_encode([
            'ten' => $ten,
            'loi_nhan' => $loi_nhan,
            'thoi_gian' => date('d/m/Y H:i:s')
        ]) . PHP_EOL;

        // Lưu vào tệp an toàn với cơ chế khóa
        file_put_contents($file_luu_but, $data, FILE_APPEND | LOCK_EX);

        // PRG: Chuyển hướng sau khi POST thành công để tránh submit lại khi F5
        header("Location: gioithieu.php?status=success");
        exit;
    } else {
        $loi = 'Vui lòng nhập đầy đủ tên và lời nhắn.';
    }
}

// Đọc tối đa 5 lời nhắn mới nhất
$danh_sach_luu_but = [];
if (file_exists($file_luu_but)) {
    $lines = file($file_luu_but, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // Đảo ngược để đưa tin mới nhất lên đầu
    $lines = array_slice($lines, 0, 5); // Cắt lấy 5 dòng
    foreach ($lines as $line) {
        $danh_sach_luu_but[] = json_decode($line, true);
    }
}
?>

<!-- HTML Bắt đầu -->
<!-- Thay thế bằng đường dẫn tới file CSS cá nhân của bạn nếu cần -->
<!-- <link rel="stylesheet" href="css/style.css"> -->

<main class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5">Trang cá nhân - Trần Xuân Sang</h1>
            <p class="lead">Sinh viên Công nghệ Thông tin - Đại học Đà Nẵng</p>
        </div>
    </div>

    <hr>

    <!-- Hiển thị Chức năng 1 -->
    <section class="mb-5">
        <h2 class="mb-3">Dự án chuyên môn</h2>
        <div class="row">
            <?php foreach ($du_an as $da): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?= e($da['ten']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= e($da['cong_nghe']) ?></h6>
                            <p class="card-text mt-3"><?= e($da['mo_ta']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <hr>

    <!-- Hiển thị Chức năng 2 -->
    <section class="mb-5">
        <h2 class="mb-4">Sổ lưu bút</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success" role="alert">
                Cảm ơn bạn đã để lại lời nhắn!
            </div>
        <?php endif; ?>

        <?php if ($loi !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= e($loi) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Viết lời nhắn</h5>
                        <form method="POST" action="gioithieu.php">
                            <div class="mb-3">
                                <label for="ten" class="form-label">Tên của bạn</label>
                                <input type="text" class="form-control" id="ten" name="ten" required>
                            </div>
                            <div class="mb-3">
                                <label for="loi_nhan" class="form-label">Lời nhắn</label>
                                <textarea class="form-control" id="loi_nhan" name="loi_nhan" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Gửi lời nhắn</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="mb-3">5 Lời nhắn mới nhất</h5>
                <ul class="list-group shadow-sm">
                    <?php if (empty($danh_sach_luu_but)): ?>
                        <li class="list-group-item text-muted">Chưa có lời nhắn nào. Hãy là người đầu tiên!</li>
                    <?php else: ?>
                        <?php foreach ($danh_sach_luu_but as $lb): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <strong class="text-primary"><?= e($lb['ten']) ?></strong>
                                    <small class="text-muted"><?= e($lb['thoi_gian']) ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?= e($lb['loi_nhan']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </section>
</main>

<!-- Gọi script cá nhân -->
<script src="js/canhan.js"></script>

<?php require $goc . 'inc/footer.php'; ?>