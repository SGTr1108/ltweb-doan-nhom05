<?php
/**
 * Tên tệp: gioithieu.php
 * Mô tả: Trang giới thiệu cá nhân sinh viên Nguyễn Võ Khánh Linh, tích hợp 
 *        giao diện sáng/tối ghi nhớ bằng Cookie và nhật ký truy cập server.
 * Cách thử: Mở trang qua localhost, kiểm tra nhật ký truy cập và bấm nút 
 *           chuyển giao diện để xem cookie hoạt động.
 */

// Hàm rút gọn in dữ liệu an toàn chống XSS
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 1. Xử lý ghi nhật ký truy cập cá nhân vào file nhatky.txt trên server
$logFile = 'nhatky.txt';
$time = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Không rõ';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Không rõ';
$logEntry = "[$time] - IP: $ip - Trình duyệt: $userAgent\n";

// Ghi log vào file (tự động tạo file nếu chưa có)
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Đọc lại nhật ký để hiển thị (lấy tối đa 5 lượt truy cập gần nhất)
$logs = [];
if (file_exists($logFile)) {
    $allLogs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($allLogs !== false) {
        $logs = array_slice(array_reverse($allLogs), 0, 5);
    }
}

// 2. Xử lý giao diện Sáng / Tối thông qua Cookie
$currentTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Trang giới thiệu cá nhân sinh viên thực hiện đồ án MiniPos."
    />
    <title>Giới thiệu Cá nhân | MiniPos</title>

    <!-- 1. Nhúng CSS dùng chung của nhóm -->
    <link rel="stylesheet" href="../../css/style.css" />

    <!-- 2. Nhúng CSS riêng của cá nhân -->
    <link rel="stylesheet" href="profile.css" />
  </head>

  <!-- Gán class dark-mode trực tiếp vào body nếu cookie là dark -->
  <body class="<?php echo $currentTheme === 'dark' ? 'dark-mode' : ''; ?>">
    <header>
      <h1>Trang giới thiệu cá nhân</h1>
    </header>

    <nav>
      <!-- Liên kết hai chiều -->
      <a href="../../index.php">Quay về Trang chủ Nhóm</a> |
      <a href="../../gioi-thieu.php">Quay lại Giới thiệu Nhóm</a>
    </nav>

    <main>
      <!-- Section: Giới thiệu bản thân và ảnh chân dung -->
      <section class="profile-header">
        <!-- Cột 1: Ảnh chân dung -->
        <div class="profile-avatar">
          <img
            src="linh_avatar.png"
            alt="Ảnh chân dung sinh viên Nguyễn Võ Khánh Linh"
            class="profile-img"
          />
        </div>

        <!-- Cột 2: Nội dung giới thiệu -->
        <div class="profile-info">
          <h2>Thông tin sinh viên</h2>
          <div class="tuong-tac-ca-nhan">
            <p id="loi-chao" class="loi-chao" aria-live="polite">
              👋 Xin chào!
            </p>
            <button id="nut-giao-dien" type="button" aria-pressed="<?php echo $currentTheme === 'dark' ? 'true' : 'false'; ?>">
              <?php echo $currentTheme === 'dark' ? '☀️ Chuyển sang giao diện sáng (>∇<)ﾉ' : '🌙 Chuyển sang giao diện tối (￣o￣) . z Z'; ?>
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
              <li><small><?php echo e($logItem); ?></small></li>
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
              <!-- Tiết 1 -->
              <tr>
                <td class="period">1</td>
                <td></td>
                <td></td>
                <td></td>
                <td rowspan="3">
                  31231330 - 24-0101<br />
                  Khai phá dữ liệu<br />
                  Phòng: A5-404B
                </td>
                <td rowspan="3">
                  31231398 - 24-0101<br />
                  Lập trình mạng<br />
                  Phòng: A5-403
                </td>
                <td rowspan="3">
                  31231755 - 24-0102<br />
                  Thiết kế và lập trình web<br />
                  Phòng: B3-303
                </td>
                <td></td>
              </tr>
              <!-- Tiết 2 -->
              <tr>
                <td class="period">2</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 3 -->
              <tr>
                <td class="period">3</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 4 -->
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
              <!-- Tiết 5 -->
              <tr>
                <td class="period">5</td>
                <td></td>
                <td rowspan="2">
                  21221904 - 24-0113<br />
                  Lịch sử Đảng Cộng sản Việt Nam<br />
                  Phòng: A6-403
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 6 -->
              <tr>
                <td class="period">6</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Khoảng trống giữa tiết 6 và 7 -->
              <tr class="break-row">
                <td colspan="8"></td>
              </tr>
              <!-- Tiết 7 -->
              <tr>
                <td class="period">7</td>
                <td></td>
                <td rowspan="3">
                  31241283 - 24-0103<br />
                  Hệ quản trị cơ sở dữ liệu<br />
                  Phòng: B3-304
                </td>
                <td></td>
                <td rowspan="3">
                  31231016 - 24-0102<br />
                  Công nghệ phần mềm<br />
                  Phòng: B3-503
                </td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 8 -->
              <tr>
                <td class="period">8</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 9 -->
              <tr>
                <td class="period">9</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <!-- Tiết 10 -->
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
              <!-- Tiết 11 -->
              <tr>
                <td class="period">11</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td rowspan="2">
                  31221010 - 24-0103<br />
                  An toàn thông tin<br />
                  Phòng: B3-506
                </td>
                <td></td>
              </tr>
              <!-- Tiết 12 -->
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

    <footer>
      <p>
        &copy; 2026 [Nguyễn Võ Khánh Linh] - Khoa Toán - Tin, Đại học Sư phạm Đà
        Nẵng.
      </p>
    </footer>

    <!-- File JavaScript điều khiển -->
    <script src="js/canhan.js"></script>
  </body>
</html>