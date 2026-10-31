<?php
/*
 * Chức năng: Trang cá nhân MiniPos, tải ảnh và theo dõi hạn nộp bài.
 * Cách thử: Mở trang, tải ảnh, thêm bài tập, đánh dấu hoàn thành hoặc xóa.
 * Dữ liệu hạn nộp được lưu trong storage/han-nop-luy.json.
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

$tepHanNopLuy = __DIR__ . '/../../storage/han-nop-luy.json';
$danhSachHanNopLuy = [];

if (is_file($tepHanNopLuy) && is_readable($tepHanNopLuy)) {
    $duLieuHanNop = file_get_contents($tepHanNopLuy);
    $duLieuGiaiMa = is_string($duLieuHanNop)
        ? json_decode($duLieuHanNop, true)
        : null;

    if (is_array($duLieuGiaiMa)) {
        $danhSachHanNopLuy = $duLieuGiaiMa;
    }
}

if (
$_SERVER['REQUEST_METHOD'] === 'POST'
&& ($_POST['hanh_dong_han_nop'] ?? '') === 'them'
) {
$tokenHanNop = is_string($_POST['csrf_han_nop'] ?? null)
? $_POST['csrf_han_nop'] : '';
$tenBai = trim(is_string($_POST['ten_bai'] ?? null)
? $_POST['ten_bai'] : '');
$ngayHan = is_string($_POST['ngay_han'] ?? null)
? $_POST['ngay_han'] : '';

if (!hash_equals($_SESSION['csrf_han_nop_luy'], $tokenHanNop)) {
    $thongBaoHanNopLuy = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
} elseif ($tenBai === '' || mb_strlen($tenBai) > 120) {
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
    $danhSachHanNopLuy[] = [
        'id' => bin2hex(random_bytes(8)),
        'ten_bai' => $tenBai,
        'ngay_han' => $ngayHan,
        'ngay_tao' => date('Y-m-d H:i:s'),
    ];

    $noiDungJson = json_encode(
        $danhSachHanNopLuy,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if (
        is_string($noiDungJson)
        && file_put_contents($tepHanNopLuy, $noiDungJson, LOCK_EX) !== false
    ) {
        $thongBaoHanNopLuy = 'Đã thêm bài tập cần nộp thành công.';
    } else {
        array_pop($danhSachHanNopLuy);
        $thongBaoHanNopLuy = 'Không thể lưu dữ liệu. Hãy kiểm tra thư mục storage.';
    }
}

$_SESSION['thong_bao_han_nop_luy'] = $thongBaoHanNopLuy;
$_SESSION['csrf_han_nop_luy'] = bin2hex(random_bytes(32));

header('Location: gioithieu.php#han-nop-bai', true, 303);
exit;

}

/* Xử lý đánh dấu hoàn thành hoặc xóa bài tập */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($_POST['hanh_dong_han_nop'] ?? '', ['hoan_thanh', 'xoa'], true)
) {
    $hanhDong = (string) $_POST['hanh_dong_han_nop'];
    $tokenGui = is_string($_POST['csrf_han_nop'] ?? null)
        ? $_POST['csrf_han_nop'] : '';
    $idBai = is_string($_POST['id_bai'] ?? null)
        ? $_POST['id_bai'] : '';

    if (!hash_equals($_SESSION['csrf_han_nop_luy'], $tokenGui)) {
        $thongBaoHanNopLuy = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    } elseif ($idBai === '') {
        $thongBaoHanNopLuy = 'Không tìm thấy bài tập cần thao tác.';
    } else {
        $danhSachMoi = $danhSachHanNopLuy;
        $timThay = false;

        foreach ($danhSachMoi as $i => $bai) {
            if (
                is_array($bai)
                && isset($bai['id'])
                && is_string($bai['id'])
                && hash_equals($bai['id'], $idBai)
            ) {
                $timThay = true;
                if ($hanhDong === 'xoa') {
                    unset($danhSachMoi[$i]);
                    $thongBaoHanNopLuy = 'Đã xóa bài tập.';
                } else {
                    $daHoanThanh = ($bai['hoan_thanh'] ?? false) === true;
                    $danhSachMoi[$i]['hoan_thanh'] = !$daHoanThanh;
                    $thongBaoHanNopLuy = !$daHoanThanh
                        ? 'Đã đánh dấu bài tập hoàn thành.'
                        : 'Đã chuyển bài tập về trạng thái chưa hoàn thành.';
                }
                break;
            }
        }

        if (!$timThay) {
            $thongBaoHanNopLuy = 'Không tìm thấy bài tập này.';
        } else {
            $noiDungJson = json_encode(
                array_values($danhSachMoi),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            if (
                !is_string($noiDungJson)
                || file_put_contents($tepHanNopLuy, $noiDungJson, LOCK_EX) === false
            ) {
                $thongBaoHanNopLuy = 'Không thể lưu dữ liệu. Hãy kiểm tra thư mục storage.';
            }
        }
    }

    $_SESSION['thong_bao_han_nop_luy'] = $thongBaoHanNopLuy;
    $_SESSION['csrf_han_nop_luy'] = bin2hex(random_bytes(32));
    header('Location: gioithieu.php#han-nop-bai', true, 303);
    exit;
}

/* Chỉ nạp header sau khi đã xử lý xong POST */
require_once __DIR__ . '/../../inc/header.php';
?>

<main class="trang-ca-nhan-luy">
    <div class="luy-profile-title">
        <h1>Trang giới thiệu cá nhân</h1>
    </div>
    


    <!-- Section: Giới thiệu bản thân và ảnh chân dung -->
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

        <!-- Thêm id="typing-text" và thuộc tính data-text để JS đọc dữ liệu -->
        <p id="typing-text"
          data-text="Chào mừng bạn đến với trang cá nhân của mình trong dự án phát triển hệ thống quản lý bán hàng MiniPos.">
          <!-- Mặc định để trống, JS sẽ gõ chữ vào đây -->
        </p>

        <p><strong>Kỹ năng chuyên môn</strong></p>
        <ul>
          <li>Ngôn ngữ: HTML, PHP cơ bản</li>
          <li>Công cụ: Visual Studio Code, Git/GitHub, XAMPP, Word, Draw.io</li>
          <li>Cơ sở dữ liệu: MySQL</li>
        </ul>
      </div>
    </section>

    <!-- Chức năng Accordion: Dự án và Sở thích -->
    <article class="accordion-section">
      <!-- Biến h2 thành một nút có thể click và điều hướng bằng phím Tab/Enter -->
      <h2>
        <button class="accordion-header" aria-expanded="true" aria-controls="content-dinhhuong">
          Định hướng và Sở thích
        </button>
      </h2>

      <!-- Bọc nội dung cần đóng/mở vào trong div -->
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

          <!-- Tiết 1 -->
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

          <!-- Tiết 2 -->
          <tr>
            <th scope="row">2</th>

            <td></td>
            <td></td>
            <td></td>

            <td></td>
          </tr>

          <!-- Tiết 3 -->
          <tr>
            <th scope="row">3</th>

            <td></td>
            <td></td>
            <td></td>

            <td></td>
          </tr>

          <!-- Tiết 4 -->
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

          <!-- Tiết 5 -->
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

          <!-- Tiết 6 -->
          <tr>
            <th scope="row">6</th>

            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 7 -->
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

          <!-- Tiết 8 -->
          <tr>
            <th scope="row">8</th>

            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 9 -->
          <tr>
            <th scope="row">9</th>

            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 10 -->
          <tr>
            <th scope="row">10</th>

            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 11 -->
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

          <!-- Tiết 12 -->
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

<section id="han-nop-bai" class="luy-han-nop-bai"> <h2>Theo dõi hạn nộp bài</h2> <p>Thêm bài tập và theo dõi thời hạn hoàn thành của bạn.</p>

<form action="gioithieu.php#han-nop-bai" method="post">
    <input type="hidden" name="hanh_dong_han_nop" value="them">
    <input type="hidden" name="csrf_han_nop"
           value="<?= e($_SESSION['csrf_han_nop_luy']) ?>">

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
                || !is_string($bai['id'])
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
                || ($loiNgay !== false && ($loiNgay['warning_count'] > 0 || $loiNgay['error_count'] > 0))
                || $hanNop->format('Y-m-d') !== $ngayHanBai
            ) {
                continue;
            }
            $homNay = new DateTimeImmutable('today');
            $soNgay = (int) $homNay->diff($hanNop)->format('%r%a');
            $daHoanThanh = ($bai['hoan_thanh'] ?? false) === true;
            ?>
            <li>
                <strong><?= e($bai['ten_bai']) ?></strong>
                <?php if ($daHoanThanh): ?>
                    <span> — Đã hoàn thành</span>
                <?php else: ?>
                    <span> — Hạn nộp: <?= e($ngayHanBai) ?>.
                    <?php if ($soNgay > 0): ?>Còn <?= $soNgay ?> ngày.
                    <?php elseif ($soNgay === 0): ?>Hạn nộp hôm nay!
                    <?php else: ?>Đã quá hạn <?= abs($soNgay) ?> ngày.
                    <?php endif; ?></span>
                <?php endif; ?>
                <form action="gioithieu.php#han-nop-bai" method="post">
                    <input type="hidden" name="csrf_han_nop" value="<?= e($_SESSION['csrf_han_nop_luy']) ?>">
                    <input type="hidden" name="id_bai" value="<?= e($bai['id']) ?>">
                    <button type="submit" name="hanh_dong_han_nop" value="hoan_thanh">
                        <?= $daHoanThanh ? 'Bỏ hoàn thành' : 'Đánh dấu hoàn thành' ?>
                    </button>
                    <button type="submit" name="hanh_dong_han_nop" value="xoa"
                        onclick="return confirm('Bạn có chắc muốn xóa bài tập này không?')">Xóa</button>
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