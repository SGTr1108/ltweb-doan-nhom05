<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

yeuCauQuyen('quan_tri');

$spId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($spId === false || $spId < 1) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$khoSanPham = new \App\Data\KhoSanPham(db());
$sanPham    = $khoSanPham->timTheoId($spId);

if ($sanPham === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$khoDanhMuc  = new \App\Data\KhoDanhMuc(db());
$danhMucList = $khoDanhMuc->tatCa();

$loi = [];
$old = [
    'ten'         => $sanPham->getTen(),
    'danh_muc_id' => (string) $sanPham->getDanhMucId(),
    'gia'         => (string) $sanPham->getGia(),
    'ton_kho'     => (string) $sanPham->getTonKho(),
    'mo_ta'       => $sanPham->getMoTa(),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();

    $old['ten']         = trim((string) ($_POST['ten'] ?? ''));
    $old['danh_muc_id'] = (string) ($_POST['danh_muc_id'] ?? '');
    $old['gia']         = (string) ($_POST['gia'] ?? '');
    $old['ton_kho']     = (string) ($_POST['ton_kho'] ?? '0');
    $old['mo_ta']       = trim((string) ($_POST['mo_ta'] ?? ''));

    if ($old['ten'] === '') {
        $loi['ten'] = 'Vui lòng nhập tên sản phẩm.';
    }

    $dmId = filter_var($old['danh_muc_id'], FILTER_VALIDATE_INT);
    if ($dmId === false || !$khoDanhMuc->tonTai($dmId)) {
        $loi['danh_muc_id'] = 'Danh mục không tồn tại.';
    }

    $gia = filter_var($old['gia'], FILTER_VALIDATE_INT);
    if ($gia === false || $gia < 1000) {
        $loi['gia'] = 'Giá phải là số nguyên tối thiểu 1.000đ.';
    }

    $tonKho = filter_var($old['ton_kho'], FILTER_VALIDATE_INT);
    if ($tonKho === false || $tonKho < 0) {
        $loi['ton_kho'] = 'Tồn kho phải là số nguyên không âm.';
    }

    // Tải ảnh mới nếu có
    $tepAnh      = $_FILES['anh'] ?? null;
    $duongDanAnh = null;

    if (is_array($tepAnh) && ($tepAnh['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($tepAnh['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Lỗi tải ảnh lên.';
        } else {
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($tepAnh['tmp_name']);
            $cacMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!isset($cacMime[$mime])) {
                $loi['anh'] = 'Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.';
            } elseif ($tepAnh['size'] > 2 * 1024 * 1024) {
                $loi['anh'] = 'Dung lượng ảnh tối đa 2MB.';
            } else {
                $tenTep = 'sp_' . bin2hex(random_bytes(8)) . '.' . $cacMime[$mime];
                if (move_uploaded_file($tepAnh['tmp_name'], __DIR__ . '/images/' . $tenTep)) {
                    $duongDanAnh = 'images/' . $tenTep;
                } else {
                    $loi['anh'] = 'Không thể lưu tệp ảnh.';
                }
            }
        }
    }

    if ($loi === []) {
        $khoSanPham->capNhat($spId, [
            'danh_muc_id' => $dmId,
            'ten'         => $old['ten'],
            'gia'         => $gia,
            'ton_kho'     => $tonKho,
            'anh'         => $duongDanAnh, // null giữ nguyên ảnh cũ
            'mo_ta'       => $old['mo_ta'] !== '' ? $old['mo_ta'] : null,
        ]);

        $_SESSION['flash_quan_tri'] = "Đã cập nhật sản phẩm #{$spId} thành công!";
        header('Location: quan-tri.php', true, 303);
        exit;
    }
}

$tieuDeTrang  = "Sửa sản phẩm #{$spId} | MiniPos";
$moTaTrang    = "Chỉnh sửa thông tin sản phẩm #{$spId}.";
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Cập nhật sản phẩm #<?= e($spId) ?></h1>

    <form action="sua-san-pham.php?id=<?= e($spId) ?>" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <p>
            <label for="ten">Tên sản phẩm *:</label>
            <input type="text" id="ten" name="ten" value="<?= e($old['ten']) ?>" required>
            <?php if (isset($loi['ten'])): ?><span class="thong-bao-loi" role="alert"><?= e($loi['ten']) ?></span><?php endif; ?>
        </p>

        <p>
            <label for="danh_muc_id">Danh mục *:</label>
            <select id="danh_muc_id" name="danh_muc_id" required>
                <?php foreach ($danhMucList as $dm): ?>
                    <option value="<?= e($dm['id']) ?>" <?= (string)$dm['id'] === $old['danh_muc_id'] ? 'selected' : '' ?>>
                        <?= e($dm['ten']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($loi['danh_muc_id'])): ?><span class="thong-bao-loi" role="alert"><?= e($loi['danh_muc_id']) ?></span><?php endif; ?>
        </p>

        <p>
            <label for="gia">Giá bán (VNĐ) *:</label>
            <input type="number" id="gia" name="gia" min="1000" step="500" value="<?= e($old['gia']) ?>" required>
            <?php if (isset($loi['gia'])): ?><span class="thong-bao-loi" role="alert"><?= e($loi['gia']) ?></span><?php endif; ?>
        </p>

        <p>
            <label for="ton_kho">Số lượng tồn kho *:</label>
            <input type="number" id="ton_kho" name="ton_kho" min="0" value="<?= e($old['ton_kho']) ?>" required>
            <?php if (isset($loi['ton_kho'])): ?><span class="thong-bao-loi" role="alert"><?= e($loi['ton_kho']) ?></span><?php endif; ?>
        </p>

        <p>
            <label>Ảnh hiện tại:</label><br>
            <img src="<?= e($sanPham->getAnh() ?: 'images/MiniPOS.webp') ?>" width="100" alt="Ảnh hiện tại"><br>
            <label for="anh">Tải ảnh mới (để trống nếu giữ ảnh cũ):</label>
            <input type="file" id="anh" name="anh" accept="image/jpeg,image/png,image/webp">
            <?php if (isset($loi['anh'])): ?><span class="thong-bao-loi" role="alert"><?= e($loi['anh']) ?></span><?php endif; ?>
        </p>

        <p>
            <label for="mo_ta">Mô tả sản phẩm:</label><br>
            <textarea id="mo_ta" name="mo_ta" rows="4" cols="50"><?= e($old['mo_ta']) ?></textarea>
        </p>

        <button type="submit">Lưu thay đổi</button>
        <a href="quan-tri.php">Hủy bỏ</a>
    </form>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>