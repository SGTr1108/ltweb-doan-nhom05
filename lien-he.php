<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$thongBao = (string) ($_SESSION['flash_lien_he'] ?? '');
unset($_SESSION['flash_lien_he']);

$errors = $_SESSION['errors_lien_he'] ?? [];
$old = $_SESSION['old_lien_he'] ?? [
    'manhanvien' => '',
    'email' => '',
    'ngaybaocao' => '',
    'mucdouutien' => '3',
    'noidung' => '',
    'loaiyeucau' => 'loi',
];

unset($_SESSION['errors_lien_he'], $_SESSION['old_lien_he']);

$_SESSION['csrf_lien_he'] ??= bin2hex(random_bytes(32));
$csrfToken = $_SESSION['csrf_lien_he'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'manhanvien' => is_string($_POST['manhanvien'] ?? null)
            ? trim($_POST['manhanvien']) : '',
        'email' => is_string($_POST['email'] ?? null)
            ? trim($_POST['email']) : '',
        'ngaybaocao' => is_string($_POST['ngaybaocao'] ?? null)
            ? trim($_POST['ngaybaocao']) : '',
        'mucdouutien' => is_string($_POST['mucdouutien'] ?? null)
            ? trim($_POST['mucdouutien']) : '',
        'noidung' => is_string($_POST['noidung'] ?? null)
            ? trim($_POST['noidung']) : '',
        'loaiyeucau' => is_string($_POST['loaiyeucau'] ?? null)
            ? $_POST['loaiyeucau'] : '',
    ];

    $errors = [];

    $tokenGui = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token'] : '';

    if (!hash_equals($csrfToken, $tokenGui)) {
        $errors['chung'] = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    }

    if (!preg_match('/\ANV[0-9]{3}\z/', $old['manhanvien'])) {
        $errors['manhanvien'] = 'Mã nhân viên phải có dạng NV001.';
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng.';
    }

    $ngay = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $old['ngaybaocao']
    );
    $loiNgay = DateTimeImmutable::getLastErrors();

    if (
        $ngay === false
        || (
            $loiNgay !== false
            && (
                $loiNgay['warning_count'] > 0
                || $loiNgay['error_count'] > 0
            )
        )
        || $ngay->format('Y-m-d') !== $old['ngaybaocao']
    ) {
        $errors['ngaybaocao'] = 'Vui lòng chọn ngày báo cáo hợp lệ.';
    }

    $mucDo = filter_var(
        $old['mucdouutien'],
        FILTER_VALIDATE_INT
    );

    if ($mucDo === false || $mucDo < 1 || $mucDo > 5) {
        $errors['mucdouutien'] = 'Mức độ khẩn cấp phải là số từ 1 đến 5.';
    }

    if ($old['noidung'] === '') {
        $errors['noidung'] = 'Vui lòng nhập nội dung báo cáo.';
    } elseif (
        (function_exists('mb_strlen')
            ? mb_strlen($old['noidung'], 'UTF-8')
            : strlen($old['noidung'])) > 3000
    ) {
        $errors['noidung'] = 'Nội dung không được vượt quá 3000 ký tự.';
    }

    if (!in_array($old['loaiyeucau'], ['loi', 'nhapkho'], true)) {
        $errors['loaiyeucau'] = 'Vui lòng chọn loại yêu cầu hợp lệ.';
    }

    // Ảnh minh chứng là tùy chọn; chỉ chấp nhận JPG, PNG và WebP.
    $tepTam = null;
    $duoiTep = null;
    $tepDaLuu = null;
    $tepTaiLen = $_FILES['anh_dinh_kem'] ?? null;

    if (is_array($tepTaiLen)) {
        $maLoiTaiTep = $tepTaiLen['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($maLoiTaiTep !== UPLOAD_ERR_NO_FILE) {
            if ($maLoiTaiTep !== UPLOAD_ERR_OK) {
                $errors['anh_dinh_kem'] =
                    'Tải ảnh không thành công. Vui lòng thử lại.';
            } else {
                $tepTam = $tepTaiLen['tmp_name'] ?? null;
                $kichThuoc = $tepTaiLen['size'] ?? 0;

                if (
                    !is_string($tepTam)
                    || !is_uploaded_file($tepTam)
                    || !is_int($kichThuoc)
                    || $kichThuoc > 2 * 1024 * 1024
                ) {
                    $errors['anh_dinh_kem'] =
                        'Ảnh phải có dung lượng tối đa 2 MB.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tepTam);

                    $cacLoaiAnh = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                    ];

                    if (!is_string($mime) || !isset($cacLoaiAnh[$mime])) {
                        $errors['anh_dinh_kem'] =
                            'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
                    } else {
                        $duoiTep = $cacLoaiAnh[$mime];
                    }
                }
            }
        }
    }

    // Lưu ảnh minh chứng sau khi dữ liệu đã hợp lệ.
    $tepDaLuu = null;

    if ($errors === [] && $tepTam !== null && $duoiTep !== null) {
        $tepDaLuu = bin2hex(random_bytes(16)) . '.' . $duoiTep;
        $duongDanAnh = __DIR__ . '/uploads/' . $tepDaLuu;

        if (!move_uploaded_file($tepTam, $duongDanAnh)) {
            $errors['anh_dinh_kem'] = 'Không thể lưu ảnh minh chứng.';
            $tepDaLuu = null;
        }
    }

    if ($errors === []) {
        $phieu = [
            'ma_phieu' => bin2hex(random_bytes(8)),
            'thoi_gian_tao' => date('c'),
            'manhanvien' => $old['manhanvien'],
            'email' => $old['email'],
            'ngaybaocao' => $old['ngaybaocao'],
            'mucdouutien' => $mucDo,
            'noidung' => $old['noidung'],
            'loaiyeucau' => $old['loaiyeucau'],
            'anh_dinh_kem' => $tepDaLuu,
        ];

        try {
            $khoLienHe = new \App\Data\KhoLienHe();
            $khoLienHe->them($phieu);
        } catch (\Throwable $loi) {
            error_log(
                'Không thể lưu phiếu báo cáo: ' . $loi->getMessage()
            );

            $errors['chung'] = 'Không thể lưu phiếu báo cáo.';
        }

        if ($errors !== [] && $tepDaLuu !== null) {
            @unlink(__DIR__ . '/uploads/' . $tepDaLuu);
        }
    }
    if ($errors !== []) {
        $_SESSION['errors_lien_he'] = $errors;
        $_SESSION['old_lien_he'] = $old;
    } else {
        $_SESSION['flash_lien_he'] =
            'Phiếu báo cáo đã được lưu thành công trên máy chủ.';
        $_SESSION['csrf_lien_he'] = bin2hex(random_bytes(32));
    }

    header('Location: lien-he.php', true, 303);
    exit;
}

$tieuDeTrang = 'Liên hệ & Báo cáo | MiniPos';
$moTaTrang = 'Biểu mẫu báo cáo sự cố hoặc yêu cầu nhập thêm hàng hóa cho kho.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Báo cáo sự cố hoặc Yêu cầu nhập kho</h1>

    <p class="mo-ta-lien-he">
        Nhân viên sử dụng biểu mẫu này để gửi yêu cầu xử lý lỗi hệ thống
        hoặc báo cáo hết hàng tới Admin.
    </p>

    <?php if ($thongBao !== ''): ?>
        <p role="status" aria-live="polite"><?= e($thongBao) ?></p>
    <?php endif; ?>

    <?php if (isset($errors['chung'])): ?>
        <p class="thong-bao-loi" role="alert"><?= e($errors['chung']) ?></p>
    <?php endif; ?>

    <form action="lien-he.php" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <fieldset>
            <legend>Thông tin phiếu báo cáo</legend>

            <p>
                <label for="manv">Mã Nhân Viên *:</label>
                <input
                    type="text"
                    id="manv"
                    name="manhanvien"
                    required
                    pattern="NV[0-9]{3}"
                    title="Định dạng hợp lệ: NV kèm 3 chữ số, ví dụ NV001"
                    value="<?= e($old['manhanvien']) ?>"
                    aria-invalid="<?= isset($errors['manhanvien']) ? 'true' : 'false' ?>">
            </p>
            <?php if (isset($errors['manhanvien'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['manhanvien']) ?></p>
            <?php endif; ?>

            <p>
                <label for="email">Email công việc *:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?= e($old['email']) ?>"
                    aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
            </p>
            <?php if (isset($errors['email'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['email']) ?></p>
            <?php endif; ?>

            <p>
                <label for="ngaybc">Ngày báo cáo:</label>
                <input
                    type="date"
                    id="ngaybc"
                    name="ngaybaocao"
                    required
                    value="<?= e($old['ngaybaocao']) ?>"
                    aria-invalid="<?= isset($errors['ngaybaocao']) ? 'true' : 'false' ?>">
            </p>
            <?php if (isset($errors['ngaybaocao'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['ngaybaocao']) ?></p>
            <?php endif; ?>

            <p>
                <label for="mucdo">Mức độ khẩn cấp (1 - 5):</label>
                <input
                    type="number"
                    id="mucdo"
                    name="mucdouutien"
                    min="1"
                    max="5"
                    required
                    value="<?= e($old['mucdouutien']) ?>"
                    aria-invalid="<?= isset($errors['mucdouutien']) ? 'true' : 'false' ?>">
            </p>
            <?php if (isset($errors['mucdouutien'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['mucdouutien']) ?></p>
            <?php endif; ?>

            <p>
                <label for="noidung">Nội dung báo cáo *:</label><br>
                <textarea
                    id="noidung"
                    name="noidung"
                    rows="5"
                    cols="50"
                    required
                    maxlength="3000"
                    placeholder="Nhập nội dung sự cố hoặc yêu cầu nhập hàng..."
                    aria-invalid="<?= isset($errors['noidung']) ? 'true' : 'false' ?>"><?= e($old['noidung']) ?></textarea>
            </p>
            <?php if (isset($errors['noidung'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['noidung']) ?></p>
            <?php endif; ?>

            <p>
                <label for="anh-dinh-kem">Ảnh minh chứng (không bắt buộc):</label>
                <input
                    type="file"
                    id="anh-dinh-kem"
                    name="anh_dinh_kem"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <small>Chỉ nhận JPG, PNG hoặc WebP; tối đa 2 MB.</small>
            </p>
            <?php if (isset($errors['anh_dinh_kem'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['anh_dinh_kem']) ?></p>
            <?php endif; ?>

            <p>Loại yêu cầu:</p>
            <ul class="loai-yeu-cau">
                <li>
                    <input
                        type="radio"
                        id="loi_ht"
                        name="loaiyeucau"
                        value="loi"
                        <?= $old['loaiyeucau'] === 'loi' ? 'checked' : '' ?>>
                    <label for="loi_ht">Lỗi phần mềm tính tiền</label>
                </li>
                <li>
                    <input
                        type="radio"
                        id="nhap_kho"
                        name="loaiyeucau"
                        value="nhapkho"
                        <?= $old['loaiyeucau'] === 'nhapkho' ? 'checked' : '' ?>>
                    <label for="nhap_kho">Yêu cầu nhập thêm hàng</label>
                </li>
            </ul>
            <?php if (isset($errors['loaiyeucau'])): ?>
                <p class="thong-bao-loi" role="alert"><?= e($errors['loaiyeucau']) ?></p>
            <?php endif; ?>

            <button type="submit">Gửi phiếu báo cáo</button>
        </fieldset>
    </form>

    <section class="thong-tin-lien-he">
        <h2>Thông tin liên hệ</h2>
        <p>
            Nếu cần hỗ trợ thêm, vui lòng liên hệ với nhóm phát triển
            MiniPos thông qua số điện thoại của trưởng nhóm - 033.816.2079
        </p>
        <p><strong>Nhóm thực hiện:</strong> Nhóm 05</p>
        <p><strong>Khoa:</strong> Toán - Tin</p>
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>