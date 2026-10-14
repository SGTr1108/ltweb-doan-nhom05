<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = 'Giới thiệu Nhóm 05 | MiniPos';
$moTaTrang = 'Thông tin nhóm phát triển dự án MiniPos và giảng viên hướng dẫn.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <div class="noi-dung-gioi-thieu">

        <h1>Giới thiệu đội ngũ phát triển MiniPos</h1>

        <p>
            Dự án được thực hiện trong học phần Thiết kế và Lập trình Web.
        </p>

        <h2>Thông tin môn học</h2>

        <p>
            <strong>Giảng viên phụ trách:</strong>
            TS. Nguyễn Hoàng Hải
        </p>

        <p>
            <strong>Nhóm thực hiện:</strong>
            Nhóm 05
        </p>

        <h2>Thành viên dự án</h2>

        <ul class="danh-sach-thanh-vien">
            <li>
                <a href="thanhvien/3120224092_luy/gioithieu.php">
                    Thới Đình Thị Thanh Lũy (3120224092) - Trưởng nhóm
                </a>
            </li>

            <li>
                <a href="thanhvien/3120224122_sang/gioithieu.php">
                    Trần Xuân Sang (3120224122)
                </a>
            </li>

            <li>
                <a href="thanhvien/3120224086_Linh/gioithieu.php">
                    Nguyễn Võ Khánh Linh (3120224086)
                </a>
            </li>

            <li>
                <a href="thanhvien/3120224149_tram/gioithieu.php">
                    Đoàn Thùy Thảo Trâm (3120224149)
                </a>
            </li>
        </ul>

    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>