<?php
/*
 * Tệp: gioithieu.php (Sinh viên: Trần Xuân Sang - 3120224122)
 * Chức năng: 
 *   1. Hiển thị thông tin cá nhân và danh sách dự án đọc từ CSDL MySQL (bảng tv_du_an) kèm tìm kiếm.
 *   2. Sổ lưu bút CSDL MySQL (bảng tv_luu_but) có phân trang, bảo vệ CSRF, chống XSS và chỉ chủ trang/quản trị viên đăng nhập mới được xóa lời nhắn.
 * Cách thử: 
 *   - Truy cập trang, thử tìm kiếm dự án theo từ khóa.
 *   - Nhập tên và lời nhắn vào Sổ lưu bút rồi bấm Gửi. Kiểm tra danh sách và phân trang.
 *   - Đăng nhập tài khoản Quản trị (admin@minipos.vn / matkhau123) rồi quay lại trang để sử dụng chức năng Xóa lời nhắn.
 */

$goc = '../../';
$tieuDeTrang = 'Giới thiệu cá nhân - Sang';
$cssTrang = 'style_canhan.css';
$scriptsTrang = ['thanhvien/3120224122_sang/js/canhan.js'];

require_once $goc . 'inc/config.php';

$mssvSang = '3120224122';
$pdo = db();
$u = nguoiDung();

// Kiểm tra quyền chủ trang / quản trị viên
$laChuTrang = ($u !== null && (($u['vai_tro'] ?? '') === 'quan_tri' || (int) ($u['id'] ?? 0) === 1));

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

// --- XỬ LÝ POST: THÊM / XÓA LỜI NHẮN SỔ LƯU BÚT ---
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();
    $hanhDong = $_POST['hanh_dong'] ?? '';

    if ($hanhDong === 'them_luu_but') {
        $ten = trim((string) ($_POST['ten'] ?? ''));
        $loiNhan = trim((string) ($_POST['loi_nhan'] ?? ''));

        if ($ten !== '' && $loiNhan !== '') {
            $st = $pdo->prepare('INSERT INTO tv_luu_but (mssv, ten_nguoi_gui, loi_nhan) VALUES (?, ?, ?)');
            $st->execute([$mssvSang, $ten, $loiNhan]);
            $_SESSION['flash_luu_but'] = 'Cảm ơn bạn đã để lại lời nhắn!';
        } else {
            $_SESSION['flash_luu_but_loi'] = 'Vui lòng nhập đầy đủ tên và lời nhắn.';
        }
    } elseif ($hanhDong === 'xoa_luu_but') {
        if (!$laChuTrang) {
            http_response_code(403);
            die('Bạn không có quyền xóa lời nhắn này.');
        }

        $idXoa = filter_var($_POST['id_luu_but'] ?? null, FILTER_VALIDATE_INT);
        if ($idXoa !== false && $idXoa > 0) {
            $st = $pdo->prepare('DELETE FROM tv_luu_but WHERE id = ? AND mssv = ?');
            $st->execute([$idXoa, $mssvSang]);
            $_SESSION['flash_luu_but'] = 'Đã xóa lời nhắn thành công.';
        }
    }

    header('Location: gioithieu.php#so-luu-but', true, 303);
    exit;
}

// --- CHỨC NĂNG 1: DANH SÁCH DỰ ÁN TỪ CSDL (tv_du_an) ---
$tuKhoaDuAn = trim((string) ($_GET['q_duan'] ?? ''));
$sqlDuAn = 'SELECT ten_du_an, cong_nghe, mo_ta FROM tv_du_an WHERE mssv = ?';
$paramDuAn = [$mssvSang];

if ($tuKhoaDuAn !== '') {
    $sqlDuAn .= ' AND (ten_du_an LIKE ? OR cong_nghe LIKE ?)';
    $like = '%' . addcslashes($tuKhoaDuAn, '%_\\') . '%';
    $paramDuAn[] = $like;
    $paramDuAn[] = $like;
}

$sqlDuAn .= ' ORDER BY id DESC';
$stDuAn = $pdo->prepare($sqlDuAn);
$stDuAn->execute($paramDuAn);
$dsDuAn = $stDuAn->fetchAll();

// --- CHỨC NĂNG 2: SỔ LƯU BÚT TỪ CSDL (tv_luu_but) KÈM PHÂN TRANG ---
$trangLuuBut = max(1, filter_var($_GET['trang_lb'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$soDongLB = 5;
$boQuaLB = ($trangLuuBut - 1) * $soDongLB;

$stDemLB = $pdo->prepare('SELECT COUNT(*) FROM tv_luu_but WHERE mssv = ?');
$stDemLB->execute([$mssvSang]);
$tongLuuBut = (int) $stDemLB->fetchColumn();
$tongTrangLB = max(1, (int) ceil($tongLuuBut / $soDongLB));

$stLB = $pdo->prepare(
    'SELECT id, ten_nguoi_gui, loi_nhan, thoi_gian_tao 
       FROM tv_luu_but 
      WHERE mssv = ? 
   ORDER BY thoi_gian_tao DESC, id DESC 
      LIMIT ? OFFSET ?'
);
$stLB->bindValue(1, $mssvSang, PDO::PARAM_STR);
$stLB->bindValue(2, $soDongLB, PDO::PARAM_INT);
$stLB->bindValue(3, $boQuaLB, PDO::PARAM_INT);
$stLB->execute();
$dsLuuBut = $stLB->fetchAll();

require_once $goc . 'inc/header.php';
?>

<!-- ================= PHẦN HTML ================= -->
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

    <!-- Section: Dự án thực tế (Đọc từ CSDL MySQL bảng tv_du_an) -->
    <section class="tabs-section">
        <h2>Dự án chuyên môn</h2>

        <form method="GET" action="gioithieu.php" style="margin-bottom: 15px;">
            <input type="text" name="q_duan" placeholder="Tìm theo tên hoặc công nghệ..." value="<?= e($tuKhoaDuAn) ?>" style="padding: 6px 10px; width: 250px;">
            <button type="submit" style="padding: 6px 12px;">Tìm kiếm</button>
            <?php if ($tuKhoaDuAn !== ''): ?>
                <a href="gioithieu.php" style="margin-left: 10px;">Xóa lọc</a>
            <?php endif; ?>
        </form>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php if ($dsDuAn === []): ?>
                <p>Không tìm thấy dự án nào phù hợp.</p>
            <?php else: ?>
                <?php foreach ($dsDuAn as $da): ?>
                    <div style="flex: 1; min-width: 250px; border: 1px solid #ccc; padding: 15px; border-radius: 8px; background: #fff;">
                        <h3 style="color: #2b5c92; margin-top: 0;"><?= e($da['ten_du_an']) ?></h3>
                        <p><strong>Công nghệ:</strong> <?= e($da['cong_nghe']) ?></p>
                        <p><?= e($da['mo_ta']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section: Sổ lưu bút (Lưu vào CSDL MySQL bảng tv_luu_but) -->
    <section class="bmi-section" id="so-luu-but">
        <h2>Sổ lưu bút</h2>

        <?php if (isset($_SESSION['flash_luu_but'])): ?>
            <p style="color: green; font-weight: bold;"><?= e($_SESSION['flash_luu_but']) ?></p>
            <?php unset($_SESSION['flash_luu_but']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_luu_but_loi'])): ?>
            <p style="color: red; font-weight: bold;"><?= e($_SESSION['flash_luu_but_loi']) ?></p>
            <?php unset($_SESSION['flash_luu_but_loi']); ?>
        <?php endif; ?>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;">
            <!-- Form Gửi -->
            <div style="flex: 1; min-width: 300px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e1e8ed;">
                <form method="POST" action="gioithieu.php#so-luu-but" class="bmi-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="hanh_dong" value="them_luu_but">

                    <label for="ten">Tên của bạn:</label>
                    <input type="text" id="ten" name="ten" required>

                    <label for="loi_nhan">Lời nhắn:</label>
                    <textarea id="loi_nhan" name="loi_nhan" rows="4" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required></textarea>

                    <button type="submit">Gửi lời nhắn</button>
                </form>
            </div>

            <!-- Danh sách -->
            <div style="flex: 1; min-width: 300px;">
                <h3 style="margin-top:0;">Lời nhắn mới nhất (Tổng: <?= $tongLuuBut ?>)</h3>
                <?php if ($dsLuuBut === []): ?>
                    <p style="color: #777;">Chưa có lời nhắn nào.</p>
                <?php else: ?>
                    <?php foreach ($dsLuuBut as $lb): ?>
                        <div style="background: #fff; border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                            <strong style="color: #2b5c92;"><?= e($lb['ten_nguoi_gui']) ?></strong>
                            <small style="color: #888; float: right;"><?= e($lb['thoi_gian_tao']) ?></small>
                            <p style="margin: 5px 0 0 0;"><?= e($lb['loi_nhan']) ?></p>

                            <?php if ($laChuTrang): ?>
                                <form method="POST" action="gioithieu.php#so-luu-but" style="margin-top: 5px; text-align: right;" onsubmit="return confirm('Bạn có chắc muốn xóa lời nhắn này?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="hanh_dong" value="xoa_luu_but">
                                    <input type="hidden" name="id_luu_but" value="<?= e($lb['id']) ?>">
                                    <button type="submit" style="background: #d9534f; color: white; border: none; padding: 2px 8px; border-radius: 3px; cursor: pointer; font-size: 12px;">Xóa lời nhắn</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($tongTrangLB > 1): ?>
                        <div style="margin-top: 10px;">
                            Trang:
                            <?php for ($i = 1; $i <= $tongTrangLB; $i++): ?>
                                <a href="gioithieu.php?trang_lb=<?= $i ?>#so-luu-but" style="<?= $i === $trangLuuBut ? 'font-weight:bold;' : '' ?>">[<?= $i ?>]</a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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

<?php require_once $goc . 'inc/footer.php'; ?>