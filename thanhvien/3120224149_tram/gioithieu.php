<?php
/*
 * Tệp: gioithieu.php (Sinh viên: Đoàn Thùy Thảo Trâm - 3120224149)
 * Chức năng: Nhật ký công nghệ / Blog và Hộp thư góp ý.
 * Cách thử:
 *   - Cách thử 1 (Nhật ký công nghệ / Blog): Điền đầy đủ thông tin vào Form gồm MSSV, Tiêu đề, 
 *     Nội dung ngắn, Nội dung chi tiết rồi bấm "Bấm để chèn dữ liệu". Hệ thống sẽ chèn dữ liệu 
 *     vào bảng `tv_blog` và hiển thị thông báo thành công hoặc lỗi hệ thống.
 *   - Cách thử 2 (Hộp thư góp ý): Điền đầy đủ thông tin vào Form gồm MSSV, Họ tên, Email, 
 *     Nội dung góp ý rồi bấm "Gửi góp ý". Hệ thống thực hiện tạm tắt khóa ngoại, sử dụng Prepared Statement 
 *     để chèn dữ liệu an toàn vào bảng `tv_gop_y`, sau đó bật lại khóa ngoại và làm mới trang.
 */


// gioithieu.php - Trang giới thiệu cá nhân gồm bộ đếm lượt xem và máy tính điểm học phần.
declare(strict_types=1);


$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "quanlydulieu";
$conn = null;

$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8mb4");

$thong_bao = ""; 

if (isset($_POST['btn_them'])) {
    $mssv              = mysqli_real_escape_string($conn, $_POST['mssv']);
    $tieu_de           = mysqli_real_escape_string($conn, $_POST['tieu_de']);
    $noi_dung_ngan     = mysqli_real_escape_string($conn, $_POST['noi_dung_ngan']);
    $noi_dung_chi_tiet = mysqli_real_escape_string($conn, $_POST['noi_dung_chi_tiet']);

    $sql = "INSERT INTO tv_blog (mssv, tieu_de, noi_dung_ngan, noi_dung_chi_tiet) 
            VALUES ('$mssv', '$tieu_de', '$noi_dung_ngan', '$noi_dung_chi_tiet')";

    try {
        if (mysqli_query($conn, $sql)) {
            $thong_bao = "<p style='color: green; font-weight: bold;'> Chèn dữ liệu thành công!</p>";
             header("Location: " . $_SERVER['PHP_SELF']);
        }
    } catch (mysqli_sql_exception $e) {
        // Chỉ hiện thông báo lỗi hệ thống chung, không phân biệt hay kiểm tra MSSV nữa
        $thong_bao = "<p style='color: red;'> Lỗi hệ thống: " . $e->getMessage() . "</p>";
    }
}

// Kiểm tra xem có thông báo thành công được lưu trong Session hay không để hiển thị popup
if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
    // Hiển thị xong thì xóa đi để lần sau tải lại trang không bị hiện lại popup
    unset($_SESSION['success_message']);
}

// Xử lý khi người dùng bấm nút Gửi
$message = "";
if (isset($_POST['btn_submit'])) {
    $mssv = $_POST['mssv'];
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];
    $noi_dung = $_POST['noi_dung'];
    $trang_thai = 0; // Mặc định

    try {
        // 1. TẮT kiểm tra ràng buộc khóa ngoại bằng query() thay vì exec()
        $conn->query("SET FOREIGN_KEY_CHECKS = 0;");

        // 2. Thực hiện chèn dữ liệu theo chuẩn mysqli
        $sql = "INSERT INTO tv_gop_y (mssv, ho_ten, email, noi_dung, trang_thai) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        // Liên kết các tham số (s = string, i = integer)
        $stmt->bind_param("ssssi", $mssv, $ho_ten, $email, $noi_dung, $trang_thai);
        $stmt->execute();


         header("Location: " . $_SERVER['PHP_SELF']);

        $message = "<div style='color: green; font-weight: bold;'>Chèn dữ liệu thành công!</div>";
        $stmt->close();
        exit();
    } catch (Exception $e) {
        $message = "<div style='color: red;'>Lỗi: " . $e->getMessage() . "</div>";
    } finally {
        // 3. Luôn luôn BẬT lại kiểm tra khóa ngoại sau khi xong
        $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

// Kiểm tra xem có thông báo thành công được lưu trong Session hay không để hiển thị popup
if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
    // Hiển thị xong thì xóa đi để lần sau tải lại trang không bị hiện lại popup
    unset($_SESSION['success_message']);
}


// --- CẤU HÌNH ĐƯỜNG DẪN VÀ GIAO DIỆN DÙNG CHUNG ---
$goc = '../../'; // Đường dẫn quay về gốc dự án từ thư mục thanhvien/3120224149_tram/
$tieuDeTrang = 'Giới thiệu Cá nhân | MiniPos';
$cssTrang = 'thanhvien/3120224149_tram/tram_style.css'; // Đường dẫn file CSS tính từ gốc dự án
$scriptsTrang = ['thanhvien/3120224149_tram/js/canhan.js']; // Đường dẫn file JS tính từ gốc dự án

// Nạp các tệp cấu hình và hàm dùng chung của nhóm
require_once $goc . 'inc/config.php';
require_once $goc . 'inc/ham.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Thoát ký tự đặc biệt: Kiểm tra nếu chưa khai báo để tránh lỗi trùng lặp */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Lấy giá trị POST dạng chuỗi */
if (!function_exists('post_str')) {
    function post_str(string $key): string
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }
}

// ==========================================
// CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM (FILE + SESSION)
// ==========================================
$counter_dir  = __DIR__ . '/storage';
$counter_file = $counter_dir . '/3120224149_counter.txt';

if (!is_dir($counter_dir)) {
    mkdir($counter_dir, 0755, true);
}

$fp = fopen($counter_file, 'c+');
$current_views = 0;
if ($fp !== false) {
    flock($fp, LOCK_EX);
    $current_views = (int)trim((string)stream_get_contents($fp));
    if (!isset($_SESSION['has_viewed'])) {
        $current_views++;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string)$current_views);
        $_SESSION['has_viewed'] = true;
    }
    flock($fp, LOCK_UN);
    fclose($fp);
}

// ==========================================
// CHỨC NĂNG 2: MÁY TÍNH ĐIỂM HỌC PHẦN (VALIDATE SERVER + PRG)
// ==========================================
$error_msg   = '';
$success_msg = '';
$diem_tb     = '';
$ket_qua     = '';
$a1 = '';
$a2 = '';
$a3 = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_diem'])) {
    $a1 = post_str('diem_a1');
    $a2 = post_str('diem_a2');
    $a3 = post_str('diem_a3');

    $n1 = str_replace(',', '.', $a1);
    $n2 = str_replace(',', '.', $a2);
    $n3 = str_replace(',', '.', $a3);

    if ($a1 === '' || $a2 === '' || $a3 === '') {
        $error_msg = 'Vui lòng nhập đầy đủ cả 3 cột điểm!';
    } elseif (!is_numeric($n1) || !is_numeric($n2) || !is_numeric($n3)) {
        $error_msg = 'Điểm nhập vào phải là một số hợp lệ!';
    } else {
        $f1 = (float)$n1;
        $f2 = (float)$n2;
        $f3 = (float)$n3;

        if ($f1 < 0 || $f1 > 10 || $f2 < 0 || $f2 > 10 || $f3 < 0 || $f3 > 10) {
            $error_msg = 'Điểm số không hợp lệ! Vui lòng nhập trong khoảng từ 0 đến 10.';
        } else {
            $tb = round((0.2 * $f1) + (0.3 * $f2) + (0.5 * $f3), 1);

            $_SESSION['flash'] = [
                'diem_tb' => $tb,
                'ket_qua' => ($tb >= 4.0) ? 'ĐẠT (Chúc mừng bạn)' : 'HỌC LẠI (Cố gắng lần sau)',
            ];
            header('Location: gioithieu.php#may-tinh-diem', true, 303);
            exit;
        }
    }
}

if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    $diem_tb     = (string)($_SESSION['flash']['diem_tb'] ?? '');
    $ket_qua     = (string)($_SESSION['flash']['ket_qua'] ?? '');
    $success_msg = 'Tính điểm thành công!';
    unset($_SESSION['flash']);
}

// ==========================================
// NẠP HEADER / FOOTER CHUNG (BẮT ĐẦU RA ĐỂ KHÔNG LẶP DOCTYPE/HTML/HEAD/BODY)
// ==========================================
// header.php tự in cả <!DOCTYPE>, <html>, <head>, <body> rồi mới đến <header>/<nav>.
// Trang này đã có khung riêng nên chỉ lấy phần nằm sau thẻ <body> của header.
ob_start();
include __DIR__ . '/../../inc/header.php';
$headerHtml = (string)ob_get_clean();

if (preg_match('/<header\b.*$/is', $headerHtml, $khop)) {
    $headerHtml = $khop[0];
} elseif (preg_match('/<body[^>]*>(.*)$/is', $headerHtml, $khop)) {
    $headerHtml = $khop[1];
} else {
    $headerHtml = (string)preg_replace(
        '#<!DOCTYPE[^>]*>|</?html[^>]*>|<head\b.*?</head>|</?body[^>]*>#is',
        '',
        $headerHtml
    );
}

// footer.php: bỏ </body> và </html> (nếu có) vì trang tự đóng một lần ở cuối.
ob_start();
require_once $goc . 'inc/footer.php';
$footerHtml = (string)preg_replace('#</body\s*>|</html\s*>#i', '', (string)ob_get_clean());

$headerHtml = (string)preg_replace(
    '/<header\b/i',
    '<header style="background: #143d75 !important; color: #ffffff !important;"',
    $headerHtml,
    1
);
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Trang giới thiệu cá nhân sinh viên Đoàn Thùy Thảo Trâm thực hiện đồ án MiniPos.">
    <title>Giới thiệu Cá nhân | MiniPos</title>
    <link rel="stylesheet" href="<?php echo e($goc); ?>css/style.css">
    <link rel="stylesheet" href="tram_style.css">
    <!-- Đoạn code CSS dùng để xóa nhanh viền dọc xanh -->
    <style>
        .page-title-banner ul li,
        .page-title-banner nav a,
        .page-title-banner [class*="menu"] li {
            border: none !important;
            border-left: none !important;
            border-right: none !important;
        }
    </style>
  </head>
  <body>
   <div class="page-title-banner">
     <?php echo $headerHtml; ?>

   <h1 style="text-align: center; width: 100%; max-width: 1170px; margin: 0 auto; background-color: #ffffff; color: #0056b3; border: 2px solid #0056b3; padding: 15px 0; border-radius: 8px;">Trang giới thiệu cá nhân</h1>


</div>
    <nav aria-label="Liên kết nhóm" style="max-width: 1170px;margin: 0 auto">
      <a href="https://sgtr1108.github.io/ltweb-doan-nhom05/index.html">Quay về Trang chủ Nhóm</a> |
      <a href="https://sgtr1108.github.io/ltweb-doan-nhom05/gioi-thieu.html">Quay lại Giới thiệu Nhóm</a>
    </nav>

    <main>
      <section class="profile-header" style="width:530px;">
        <div class="khoi-thong-tin-chinh">
          <button type="button" class="profile-img-btn" aria-label="Phóng to ảnh chân dung đại diện">
            <img src="avatar.png" alt="Ảnh chân dung của Đoàn Thùy Thảo Trâm" class="profile-img">
          </button>

          <div class="chi-tiet-ca-nhan">
            <h2>Thông tin sinh viên</h2>
            <p><strong>Họ và tên:</strong> Đoàn Thùy Thảo Trâm</p>
            <p><strong>Mã sinh viên:</strong> 3120224149</p>
            <p><strong>Lớp học phần:</strong> Thiết kế và Lập trình Web - Nhóm 05</p>
            <p>
              Chào mừng bạn đến với trang cá nhân của mình trong dự án phát triển hệ
              thống quản lý bán hàng MiniPos.
            </p>

            <p class="tieu-de-ky-nang"><strong>Kỹ năng chuyên môn</strong></p>
            <ul>
              <li>Ngôn ngữ: HTML, PHP cơ bản</li>
              <li>Công cụ: Visual Studio Code, Git/GitHub, XAMPP, Word, Draw.io</li>
              <li>Cơ sở dữ liệu: MySQL</li>
            </ul>
          </div>
        </div>
      </section>


      <article style="height: 1027px; width:600px;">
        <h2>Định hướng và Sở thích</h2>
        <p>
          Trong đồ án nhóm MiniPos, mình đảm nhận việc thiết kế và
          xây dựng cấu trúc giao diện website bằng HTML5 ngữ nghĩa,
          tổ chức nội dung và liên kết giữa các trang,
          đồng thời kiểm tra khả năng truy cập (A11y) và tối ưu hóa SEO on-page cho website.
        </p>
        <p>Sở thích cá nhân của mình là tham gia các hoạt động tập thể, tìm hiểu những kiến thức mới phục vụ cho học tập và giao lưu với bạn bè, sinh viên.</p>

        <div class="container">
          <div style="text-align: right; margin-bottom: 20px;">
            <span class="view-badge">👀 Tổng số lượt xem: <?php echo e($current_views); ?></span>
          </div>

          <div class="box" id="may-tinh-diem">
            <h3>Máy Tính Điểm Học Phần</h3>
            <p style="font-size: 0.9em; color: gray;">Công thức: ĐTB = 0.2×A1 + 0.3×A2 + 0.5×A3</p>

            <?php if ($error_msg !== ''): ?>
              <p class="alert-error">❌ <?php echo e($error_msg); ?></p>
            <?php endif; ?>

            <?php if ($success_msg !== ''): ?>
              <p class="alert-success"><?php echo e($success_msg); ?></p>
            <?php endif; ?>

            <form action="gioithieu.php" method="post">
              <div class="form-group">
                <label for="diem_a1">Điểm chuyên cần / Thái độ (A1):</label>
                <input type="text" id="diem_a1" name="diem_a1" value="<?php echo e($a1); ?>" placeholder="Ví dụ: 8.5">
              </div>
              <div class="form-group">
                <label for="diem_a2">Điểm kiểm tra giữa kỳ (A2):</label>
                <input type="text" id="diem_a2" name="diem_a2" value="<?php echo e($a2); ?>" placeholder="Ví dụ: 8.5">
              </div>
              <div class="form-group">
                <label for="diem_a3">Điểm thi kết thúc học phần (A3):</label>
                <input type="text" id="diem_a3" name="diem_a3" value="<?php echo e($a3); ?>" placeholder="Ví dụ: 8.5">
              </div>
              <button type="submit" name="tinh_diem" class="btn btn-submit">Tính điểm ngay</button>
            </form>

            <?php if ($diem_tb !== ''): ?>
              <div class="result-box">
                <p><strong>Điểm trung bình học phần:</strong> <span style="font-size: 1.2em; color: #007bff;"><?php echo e($diem_tb); ?></span></p>
                <p><strong>Kết quả xếp loại môn học:</strong> <strong><?php echo e($ket_qua); ?></strong></p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </article>

      <article class="profile-header">
         <h2>Nhập nhật ký Blog</h2>
         <br>
    <?php echo $thong_bao; ?>
    <br>
    <!-- GIAO DIỆN FORM ĐỂ NHẬP VÀ BẤM NÚT -->
    <form method="POST">
        <label>Mã số sinh viên (MSSV):</label><br>
        <input type="text" name="mssv" required ><br><br>

        <label>Tiêu đề bài viết:</label><br>
        <input type="text" name="tieu_de" required style="width: 300px;"><br><br>

        <label>Nội dung ngắn:</label><br>
        <input type="text" name="noi_dung_ngan" style="width: 300px;"><br><br>

        <label>Nội dung chi tiết:</label><br>
        <textarea name="noi_dung_chi_tiet" required rows="5" style="width: 300px;"></textarea><br><br>

        <!-- Nút bấm kích hoạt hành động chèn -->
        <button type="submit" name="btn_them">Bấm để chèn dữ liệu</button>
    </form>
</article>


<article>
    <h2>Hộp thư góp ý</h2>
    
    <!-- Hiển thị thông báo kết quả sau khi bấm nút -->
    <?php echo $message; ?>

    <form method="POST">
        <p>
            <label>Mã số sinh viên (MSSV)</label><br>
            <input type="text" name="mssv" required>
        </p>
        <p>
            <label>Họ tên:</label><br>
            <input type="text" name="ho_ten" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email">
        </p>
        <p>
            <label>Nội dung góp ý:</label><br>
            <textarea name="noi_dung" rows="5" required></textarea>
        </p>
        
        <!-- Nút bấm thực hiện thao tác -->
        <button type="submit" name="btn_submit" style="padding: 8px 15px; cursor: pointer;">
            Gửi góp ý
        </button>
    </form>


</article>
      <section>
        <h2>Thời khóa biểu tuần</h2>
        <div class="table-responsive">
          <table>
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
                <td></td><td></td><td></td>
                <td rowspan="3">31231330 - 24-0101<br>Khai phá dữ liệu<br>Phòng: A5-404B</td>
                <td rowspan="3">31231398 - 24-0101<br>Lập trình mạng<br>Phòng: A5-403</td>
                <td rowspan="3">31231755 - 24-0102<br>Thiết kế và lập trình web<br>Phòng: B3-303</td>
                <td></td>
              </tr>
              <tr>
                <th scope="row">2</th>
                <td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">3</th>
                <td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">4</th>
                <td></td><td></td><td></td>
                <td rowspan="3">31231282 - 24-0101<br>Hệ phân tán<br>Phòng: B3-303</td>
                <td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">5</th>
                <td></td>
                <td rowspan="2">21221904 - 24-0113<br>Lịch sử Đảng Cộng sản Việt Nam<br>Phòng: A6-403</td>
                <td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">6</th>
                <td></td><td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">7</th>
                <td rowspan="2">31221010 - 24-0102<br>An toàn thông tin<br>Phòng: A6-401</td>
                <td rowspan="4">31241283 - 24-0103<br>Hệ quản trị cơ sở dữ liệu<br>Phòng: B3-304</td>
                <td></td>
                <td rowspan="3">31231016 - 24-0102<br>Công nghệ phần mềm<br>Phòng: B3-503</td>
                <td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">8</th>
                <td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">9</th>
                <td></td><td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">10</th>
                <td></td><td></td><td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">11</th>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
              </tr>
              <tr>
                <th scope="row">12</th>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

   <?php echo $footerHtml; ?>

    <button type="button" id="scrollToTopBtn" title="Lên đầu trang" aria-label="Lên đầu trang">&#10148;</button>

    <div id="imageLightbox" class="lightbox" aria-hidden="true" role="dialog" aria-label="Trình xem ảnh phóng to">
      <button type="button" class="lightbox-close" aria-label="Đóng trình xem ảnh">&times;</button>
    </div>

    <script src="js/canhan.js" defer></script>

    <script>
    document.getElementById('btn-view').addEventListener('click', function() {
        // Gửi yêu cầu ngầm đến file backend xử lý (ví dụ: luot_xem.php)
        fetch('luot_xem.php', { method: 'POST' })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Log kết quả "Ghi nhận lượt xem thành công!"
            alert("Đã ghi nhận 1 lượt xem tự do!");
        })
        .catch(error => console.error('Lỗi:', error));
    });
    </script>
  </body>
</html>
