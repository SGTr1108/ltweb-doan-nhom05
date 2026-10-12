<footer class="footer-minipos-v2">
    <div class="footer-loi-cam-on">
        <span class="footer-trai-tim" aria-hidden="true">♥</span>

        <div class="footer-thong-diep">
            <p class="footer-tieu-de">
                Cảm ơn bạn đã tin tưởng MiniPos!
            </p>
            <p class="footer-mo-ta">
                Chúng tôi luôn nỗ lực mang đến trải nghiệm mua sắm tốt nhất cho bạn.
            </p>
        </div>
    </div>

    <div class="footer-thuong-hieu">
        <span class="footer-vach-ngan" aria-hidden="true"></span>

        <svg
            class="footer-bieu-tuong-cua-hang"
            viewBox="0 0 48 48"
            width="48"
            height="48"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true">
            <path d="M7 20h34l-3-12H10L7 20Z"/>
            <path d="M9 20v21h30V20"/>
            <path d="M18 41V27h12v14"/>
            <path d="M7 20c0 4 6 5 9 1 3 4 8 4 11 0 3 4 8 3 11-1"/>
        </svg>

        <div class="footer-thong-tin-nhom">
            <strong>MiniPos</strong>
            <span>Nhóm 05 – LTWeb</span>
        </div>
    </div>
</footer>

<script type="module" src="<?= e(($goc ?? '') . 'js/main.js') ?>"></script>
<script type="module" src="<?= e(($goc ?? '') . 'js/yeu-thich.js') ?>"></script>

<?php
$scriptsTrang = isset($scriptsTrang) && is_array($scriptsTrang)
    ? $scriptsTrang
    : [];

foreach ($scriptsTrang as $script) {
    if (
        is_string($script)
        && preg_match('/\Ajs\/[a-zA-Z0-9_-]+\.js\z/', $script)
    ) {
        echo '<script type="module" src="'
            . e(($goc ?? '') . $script)
            . '"></script>' . PHP_EOL;
    }
}
?>

</body>
</html>