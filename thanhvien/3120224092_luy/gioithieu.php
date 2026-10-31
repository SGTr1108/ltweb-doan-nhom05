<?php
/*
 * Chức năng: Trang cá nhân MiniPos, tải ảnh và theo dõi hạn nộp bài.
 * Dữ liệu hạn nộp và kỹ năng được lưu trong cơ sở dữ liệu.
 */
declare(strict_types=1);

$goc = '../../';
$tieuDeTrang = 'Giới thiệu cá nhân của Lũy | MiniPos';
$moTaTrang = 'Trang giới thiệu cá nhân, kỹ năng và thời khóa biểu của Lũy.';
$cssTrang = 'luy_style_php.css';

require_once __DIR__ . '/../../inc/config.php';

/* Kiểm tra tài khoản có phải chủ trang cá nhân không */
$mssvLuy = '3120224092';
$pdoHanNop = db();
$nguoiDungLuy = nguoiDung();

$stChuTrang = $pdoHanNop->prepare(
    'SELECT nguoi_dung_id FROM thanh_vien WHERE mssv = ?'
);
$stChuTrang->execute([$mssvLuy]);

$idChuTrang = $stChuTrang->fetchColumn();

$laChuTrangLuy = (
    $nguoiDungLuy !== null
    && $idChuTrang !== false
    && (int) $idChuTrang === (int) $nguoiDungLuy['id']
);

/* Xử lý tải ảnh đại diện cá nhân */
$_SESSION['csrf_avatar_luy'] ??= bin2hex(random_bytes(32));
$thongBaoAvatarLuy = $_SESSION['thong_bao_avatar_luy'] ?? '';
unset($_SESSION['thong_bao_avatar_luy']);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['hanh_dong_avatar'])
    && $_POST['hanh_dong_avatar'] === 'tai_len'
) {
    if (!$laChuTrangLuy) {
        http_response_code(403);
        exit('Bạn không có quyền thay đổi ảnh đại diện của trang này.');
    }

    $tokenGui = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token'] : '';

    $tep = $_FILES['avatar_moi'] ?? null;
    $loi = '';

    if (!hash_equals($_SESSION['csrf_avatar_luy'], $tokenGui)) {
        $loi = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    } elseif (!is_array($tep)) {
        $loi = 'Không nhận được ảnh tải lên.';
    } elseif (($tep['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $loi = 'Tải ảnh không thành công. Vui lòng chọn lại ảnh.';
    } elseif (
        !isset($tep['tmp_name'], $tep['size'])
        || !is_string($tep['tmp_name'])
        || !is_uploaded_file($tep['tmp_name'])
    ) {
        $loi = 'Tệp tải lên không hợp lệ.';
    } elseif ($tep['size'] <= 0 || $tep['size'] > 2 * 1024 * 1024) {
        $loi = 'Ảnh phải có dung lượng từ 1 byte đến 2 MB.';
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tep['tmp_name']);

        $loaiAnh = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!is_string($mime) || !isset($loaiAnh[$mime])) {
            $loi = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
        } elseif (@getimagesize($tep['tmp_name']) === false) {
            $loi = 'Tệp tải lên không phải ảnh hợp lệ.';
        } else {
            $tenTep = 'avatar_luy_' . bin2hex(random_bytes(16))
                . '.' . $loaiAnh[$mime];

            $duongDan = __DIR__ . '/../../uploads/' . $tenTep;

            if (move_uploaded_file($tep['tmp_name'], $duongDan)) {
                $_SESSION['avatar_luy'] = '../../uploads/' . $tenTep;
                $thongBaoAvatarLuy = 'Đã cập nhật ảnh đại diện thành công.';
            } else {
                $loi = 'Không thể lưu ảnh. Vui lòng thử lại.';
            }
        }
    }

    if ($loi !== '') {
        $thongBaoAvatarLuy = $loi;
    }

    $_SESSION['thong_bao_avatar_luy'] = $thongBaoAvatarLuy;
    $_SESSION['csrf_avatar_luy'] = bin2hex(random_bytes(32));

    header('Location: gioithieu.php#anh-dai-dien', true, 303);
    exit;
}

/* Xử lý theo dõi hạn nộp bài */
$_SESSION['csrf_han_nop_luy'] ??= bin2hex(random_bytes(32));
$thongBaoHanNopLuy = $_SESSION['thong_bao_han_nop_luy'] ?? '';
unset($_SESSION['thong_bao_han_nop_luy']);

$danhSachHanNopLuy = [];

if ($idChuTrang !== false) {
    $stHanNop = $pdoHanNop->prepare(
        'SELECT id, ten_bai, ngay_han, hoan_thanh
         FROM tv_han_nop
         WHERE mssv = ?
         ORDER BY ngay_han ASC, id ASC'
    );
    $stHanNop->execute([$mssvLuy]);
    $danhSachHanNopLuy = $stHanNop->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = is_string($_POST['hanh_dong_han_nop'] ?? null)
        ? $_POST['hanh_dong_han_nop'] : '';

    if (in_array($hanhDong, ['them', 'hoan_thanh', 'xoa'], true)) {
        $tokenHanNop = is_string($_POST['csrf_han_nop'] ?? null)
            ? $_POST['csrf_han_nop'] : '';

        if (!hash_equals($_SESSION['csrf_han_nop_luy'], $tokenHanNop)) {
            $thongBaoHanNopLuy = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
        } elseif (!$laChuTrangLuy) {
            http_response_code(403);
            exit('Bạn không có quyền chỉnh sửa hạn nộp bài.');
        } elseif ($hanhDong === 'them') {
            $tenBai = trim(is_string($_POST['ten_bai'] ?? null)
                ? $_POST['ten_bai'] : '');
            $ngayHan = is_string($_POST['ngay_han'] ?? null)
                ? $_POST['ngay_han'] : '';

            if ($tenBai === '' || mb_strlen($tenBai) > 120) {
                $thongBaoHanNopLuy = 'Tên bài tập không được để trống và tối đa 120 ký tự.';
            } elseif (
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngayHan)
                || !checkdate(
                    (int) substr($ngayHan, 5, 2),
                    (int) substr($ngayHan, 8, 2),
                    (int) substr($ngayHan, 0, 4)
                )
            ) {
                $thongBaoHanNopLuy = 'Vui lòng chọn ngày hạn nộp hợp lệ.';
            } else {
                $stThemHanNop = $pdoHanNop->prepare(
                    'INSERT INTO tv_han_nop (mssv, ten_bai, ngay_han)
                     VALUES (?, ?, ?)'
                );
                $stThemHanNop->execute([$mssvLuy, $tenBai, $ngayHan]);
                $thongBaoHanNopLuy = 'Đã thêm bài tập cần nộp thành công.';
            }
        } else {
            $idBai = filter_var(
                $_POST['id_bai'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($idBai === false) {
                $thongBaoHanNopLuy = 'Không tìm thấy bài tập cần thao tác.';
            } elseif ($hanhDong === 'xoa') {
                $stXoaHanNop = $pdoHanNop->prepare(
                    'DELETE FROM tv_han_nop WHERE id = ? AND mssv = ?'
                );
                $stXoaHanNop->execute([$idBai, $mssvLuy]);
                $thongBaoHanNopLuy = $stXoaHanNop->rowCount() > 0
                    ? 'Đã xóa bài tập.'
                    : 'Không tìm thấy bài tập này.';
            } else {
                $stDoiTrangThai = $pdoHanNop->prepare(
                    'UPDATE tv_han_nop
                     SET hoan_thanh = IF(hoan_thanh = 1, 0, 1)
                     WHERE id = ? AND mssv = ?'
                );
                $stDoiTrangThai->execute([$idBai, $mssvLuy]);
                $thongBaoHanNopLuy = $stDoiTrangThai->rowCount() > 0
                    ? 'Đã cập nhật trạng thái bài tập.'
                    : 'Không tìm thấy bài tập này.';
            }
        }

        $_SESSION['thong_bao_han_nop_luy'] = $thongBaoHanNopLuy;
        $_SESSION['csrf_han_nop_luy'] = bin2hex(random_bytes(32));

        header('Location: gioithieu.php#han-nop-bai', true, 303);
        exit;
    }
}

/* Xử lý thêm kỹ năng */
$_SESSION['csrf_ky_nang_luy'] ??= bin2hex(random_bytes(32));
$thongBaoKyNangLuy = $_SESSION['thong_bao_ky_nang_luy'] ?? '';
unset($_SESSION['thong_bao_ky_nang_luy']);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['hanh_dong_ky_nang'] ?? '') === 'them'
) {
    if (!$laChuTrangLuy) {
        http_response_code(403);
        exit('Bạn không có quyền chỉnh sửa kỹ năng của trang này.');
    }

    $tokenKyNang = is_string($_POST['csrf_ky_nang'] ?? null)
        ? $_POST['csrf_ky_nang'] : '';

    $tenKyNang = trim(is_string($_POST['ten_ky_nang'] ?? null)
        ? $_POST['ten_ky_nang'] : '');
    $nhomKyNang = trim(is_string($_POST['nhom_ky_nang'] ?? null)
        ? $_POST['nhom_ky_nang'] : '');
    $mucDoKyNang = trim(is_string($_POST['muc_do'] ?? null)
        ? $_POST['muc_do'] : '');
    $moTaKyNang = trim(is_string($_POST['mo_ta'] ?? null)
        ? $_POST['mo_ta'] : '');

    if (!hash_equals($_SESSION['csrf_ky_nang_luy'], $tokenKyNang)) {
        $thongBaoKyNangLuy = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    } elseif (
        $tenKyNang === ''
        || mb_strlen($tenKyNang) > 120
        || $nhomKyNang === ''
        || mb_strlen($nhomKyNang) > 100
        || $mucDoKyNang === ''
        || mb_strlen($mucDoKyNang) > 50
    ) {
        $thongBaoKyNangLuy = 'Vui lòng nhập kỹ năng, nhóm kỹ năng và mức độ hợp lệ.';
    } else {
        $stThemKyNangLuy = $pdoHanNop->prepare(
            'INSERT INTO tv_ky_nang
                (mssv, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stThemKyNangLuy->execute([
            $mssvLuy,
            $tenKyNang,
            $nhomKyNang,
            $mucDoKyNang,
            $moTaKyNang !== '' ? $moTaKyNang : null,
        ]);

        $thongBaoKyNangLuy = 'Đã thêm kỹ năng thành công.';
    }

    $_SESSION['thong_bao_ky_nang_luy'] = $thongBaoKyNangLuy;
    $_SESSION['csrf_ky_nang_luy'] = bin2hex(random_bytes(32));

    header('Location: gioithieu.php#ky-nang', true, 303);
    exit;
}

/* Lấy danh sách kỹ năng từ cơ sở dữ liệu */
$stKyNangLuy = $pdoHanNop->prepare(
    'SELECT id, ten_ky_nang, nhom_ky_nang, muc_do, mo_ta
     FROM tv_ky_nang
     WHERE mssv = ?
     ORDER BY id ASC'
);
$stKyNangLuy->execute([$mssvLuy]);
$danhSachKyNangLuy = $stKyNangLuy->fetchAll(PDO::FETCH_ASSOC);

/* Chỉ nạp header sau khi đã xử lý xong POST */
require_once __DIR__ . '/../../inc/header.php';
?>

<main class="trang-ca-nhan-luy">
    <div class="luy-profile-title">
        <h1>Trang giới thiệu cá nhân</h1>
    </div>

    <!-- Section: Giới thiệu bản thân và ảnh chân dung -->
    <section class="luy-thong-tin-sinh-vien">
        <h2>Thông tin sinh viên</h2>

        <?php $anhDaiDienLuy = $_SESSION['avatar_luy'] ?? 'avatar.png'; ?>

        <img
            id="anh-dai-dien"
            src="<?= e($anhDaiDienLuy) ?>"
            alt="Ảnh chân dung sinh viên"
            width="120"
            height="150"
        >

        <form action="gioithieu.php#anh-dai-dien" method="post" enctype="multipart/form-data">
            <input type="hidden" name="hanh_dong_avatar" value="tai_len">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_avatar_luy']) ?>">

            <label for="avatar_moi">Chọn ảnh đại diện (JPG, PNG hoặc WebP, tối đa 2 MB):</label>
            <input
                type="file"
                id="avatar_moi"
                name="avatar_moi"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                required
            >

            <button type="submit">Cập nhật ảnh đại diện</button>
        </form>

        <?php if ($thongBaoAvatarLuy !== ''): ?>
            <p role="status"><?= e($thongBaoAvatarLuy) ?></p>
        <?php endif; ?>

        <div class="student-info">
            <p><strong>Họ và tên:</strong> Thới Đình Thị Thanh Lũy</p>
            <p><strong>Mã sinh viên:</strong> 3120224092</p>
            <p><strong>Lớp học phần:</strong> Thiết kế và Lập trình Web - Nhóm 05</p>

            <p
                id="typing-text"
                data-text="Chào mừng bạn đến với trang cá nhân của mình trong dự án phát triển hệ thống quản lý bán hàng MiniPos."
            ></p>

            <!-- Kỹ năng chuyên môn -->
            <section id="ky-nang">
                <h3>Kỹ năng chuyên môn</h3>

                <?php if ($laChuTrangLuy): ?>
                    <form action="gioithieu.php#ky-nang" method="post">
                        <input type="hidden" name="hanh_dong_ky_nang" value="them">
                        <input
                            type="hidden"
                            name="csrf_ky_nang"
                            value="<?= e($_SESSION['csrf_ky_nang_luy']) ?>"
                        >

                        <div>
                            <label for="ten_ky_nang">Tên kỹ năng</label>
                            <input
                                type="text"
                                id="ten_ky_nang"
                                name="ten_ky_nang"
                                maxlength="120"
                                required
                                placeholder="Ví dụ: HTML"
                            >
                        </div>

                        <div>
                            <label for="nhom_ky_nang">Nhóm kỹ năng</label>
                            <input
                                type="text"
                                id="nhom_ky_nang"
                                name="nhom_ky_nang"
                                maxlength="100"
                                required
                                placeholder="Ví dụ: Lập trình Web"
                            >
                        </div>

                        <div>
                            <label for="muc_do">Mức độ</label>
                            <input
                                type="text"
                                id="muc_do"
                                name="muc_do"
                                maxlength="50"
                                required
                                placeholder="Ví dụ: Cơ bản"
                            >
                        </div>

                        <div>
                            <label for="mo_ta">Mô tả (không bắt buộc)</label>
                            <textarea
                                id="mo_ta"
                                name="mo_ta"
                                rows="3"
                                placeholder="Mô tả thêm về kỹ năng"
                            ></textarea>
                        </div>

                        <button type="submit">Thêm kỹ năng</button>
                    </form>
                <?php endif; ?>

                <?php if ($thongBaoKyNangLuy !== ''): ?>
                    <p role="status"><?= e($thongBaoKyNangLuy) ?></p>
                <?php endif; ?>

                <?php if (empty($danhSachKyNangLuy)): ?>
                    <p>Chưa có kỹ năng nào được thêm.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($danhSachKyNangLuy as $kyNangLuy): ?>
                            <li>
                                <strong><?= e($kyNangLuy['ten_ky_nang']) ?></strong>
                                — <?= e($kyNangLuy['nhom_ky_nang']) ?>,
                                <?= e($kyNangLuy['muc_do']) ?>

                                <?php if (!empty($kyNangLuy['mo_ta'])): ?>
                                    <br>
                                    <?= nl2br(e($kyNangLuy['mo_ta'])) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
    </section>

    <!-- Chức năng Accordion: Dự án và Sở thích -->
    <article class="accordion-section">
        <h2>
            <button class="accordion-header" aria-expanded="true" aria-controls="content-dinhhuong">
                Định hướng và Sở thích
            </button>
        </h2>

        <div id="content-dinhhuong" class="accordion-content open">
            <p>
                Trong đồ án nhóm MiniPos, mình đảm nhận việc lên ý tưởng và xây dựng
                cấu trúc giao diện website, phát triển các trang HTML ngữ nghĩa,
                đồng thời đảm bảo nội dung và liên kết giữa các trang được tổ chức
                rõ ràng, thống nhất.
            </p>
            <p>
                Sở thích cá nhân của mình là thực hiện các công trình nghiên cứu
                phục vụ cho học tập, tổ chức các hoạt động cho sinh viên.
            </p>
        </div>
    </article>

    <!-- Bảng: Thời khóa biểu tuần -->
    <section>
        <h2>Thời khóa biểu tuần</h2>

        <table>
            <caption>Thời khóa biểu học tập trong tuần</caption>

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
                    <th scope="row">1</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td rowspan="3">
                        31231330 - (24-0101)<br>
                        Khai phá dữ liệu<br>
                        Phòng: A5-404B
                    </td>
                    <td rowspan="3">
                        31231398 - (24-0101)<br>
                        Lập trình mạng<br>
                        Phòng: B3-303
                    </td>
                    <td rowspan="3">
                        31231755 - (24-0102)<br>
                        Thiết kế và lập trình web<br>
                        Phòng: B3-303
                    </td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">2</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">3</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">4</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td rowspan="3">
                        31231282 - (24-0101)<br>
                        Hệ phân tán<br>
                        Phòng: B3-303
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">5</th>
                    <td></td>
                    <td rowspan="2">
                        21221904 - (24-0113)<br>
                        Lịch sử Đảng Cộng sản Việt Nam<br>
                        Phòng: A6-403
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">6</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">7</th>
                    <td rowspan="2">
                        3120221010 - (24-0102)<br>
                        An toàn thông tin<br>
                        Phòng: A6-401
                    </td>
                    <td rowspan="4">
                        31241283 - (24-0103)<br>
                        Hệ quản trị cơ sở dữ liệu<br>
                        Phòng: B3-304
                    </td>
                    <td></td>
                    <td rowspan="4">
                        31231016 - (24-0102)<br>
                        Công nghệ phần mềm<br>
                        Phòng: B3-303
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">8</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">9</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">10</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">11</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th scope="row">12</th>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- Chức năng theo dõi hạn nộp bài -->
    <section id="han-nop-bai" class="luy-han-nop-bai">
        <h2>Theo dõi hạn nộp bài</h2>
        <p>Thêm bài tập và theo dõi thời hạn hoàn thành của bạn.</p>

        <form action="gioithieu.php#han-nop-bai" method="post">
            <input type="hidden" name="hanh_dong_han_nop" value="them">
            <input
                type="hidden"
                name="csrf_han_nop"
                value="<?= e($_SESSION['csrf_han_nop_luy']) ?>"
            >

            <div>
                <label for="ten_bai">Tên bài tập</label>
                <input
                    type="text"
                    id="ten_bai"
                    name="ten_bai"
                    maxlength="120"
                    required
                    placeholder="Ví dụ: Bài thực hành PHP"
                >
            </div>

            <div>
                <label for="ngay_han">Ngày hạn nộp</label>
                <input type="date" id="ngay_han" name="ngay_han" required>
            </div>

            <button type="submit">Thêm bài tập</button>
        </form>

        <?php if ($thongBaoHanNopLuy !== ''): ?>
            <p role="status"><?= e($thongBaoHanNopLuy) ?></p>
        <?php endif; ?>

        <h3>Danh sách bài cần nộp</h3>

        <?php if (empty($danhSachHanNopLuy)): ?>
            <p>Chưa có bài tập nào được thêm.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($danhSachHanNopLuy as $bai): ?>
                    <?php
                    if (
                        !is_array($bai)
                        || !isset($bai['id'], $bai['ten_bai'], $bai['ngay_han'])
                        || !is_string($bai['ten_bai'])
                        || !is_string($bai['ngay_han'])
                    ) {
                        continue;
                    }

                    $ngayHanBai = $bai['ngay_han'];
                    $hanNop = DateTimeImmutable::createFromFormat('!Y-m-d', $ngayHanBai);
                    $loiNgay = DateTimeImmutable::getLastErrors();

                    if (
                        !$hanNop
                        || ($loiNgay !== false && (
                            $loiNgay['warning_count'] > 0
                            || $loiNgay['error_count'] > 0
                        ))
                        || $hanNop->format('Y-m-d') !== $ngayHanBai
                    ) {
                        continue;
                    }

                    $homNay = new DateTimeImmutable('today');
                    $soNgay = (int) $homNay->diff($hanNop)->format('%r%a');
                    $daHoanThanh = (int) ($bai['hoan_thanh'] ?? 0) === 1;
                    ?>

                    <li>
                        <strong><?= e($bai['ten_bai']) ?></strong>

                        <?php if ($daHoanThanh): ?>
                            <span> — Đã hoàn thành</span>
                        <?php else: ?>
                            <span> — Hạn nộp: <?= e($ngayHanBai) ?>.
                                <?php if ($soNgay > 0): ?>
                                    Còn <?= $soNgay ?> ngày.
                                <?php elseif ($soNgay === 0): ?>
                                    Hạn nộp hôm nay!
                                <?php else: ?>
                                    Đã quá hạn <?= abs($soNgay) ?> ngày.
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>

                        <form action="gioithieu.php#han-nop-bai" method="post">
                            <input
                                type="hidden"
                                name="csrf_han_nop"
                                value="<?= e($_SESSION['csrf_han_nop_luy']) ?>"
                            >
                            <input
                                type="hidden"
                                name="id_bai"
                                value="<?= e($bai['id']) ?>"
                            >

                            <button
                                type="submit"
                                name="hanh_dong_han_nop"
                                value="hoan_thanh"
                            >
                                <?= $daHoanThanh ? 'Bỏ hoàn thành' : 'Đánh dấu hoàn thành' ?>
                            </button>

                            <button
                                type="submit"
                                name="hanh_dong_han_nop"
                                value="xoa"
                                onclick="return confirm('Bạn có chắc muốn xóa bài tập này không?')"
                            >Xóa</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<script type="module" src="js/canhan.js"></script>

<?php
require_once __DIR__ . '/../../inc/footer.php';
?>