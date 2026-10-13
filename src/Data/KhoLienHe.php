<?php
declare(strict_types=1);

namespace App\Data;

use JsonException;
use RuntimeException;

/**
 * Kho lưu phiếu liên hệ theo định dạng JSON Lines.
 * Mỗi phiếu là một dòng JSON riêng trong storage/lien-he.jsonl.
 * Các trang chỉ đọc và ghi dữ liệu liên hệ thông qua lớp này.
 */
final class KhoLienHe
{
    private string $duongDan;

    public function __construct(?string $duongDan = null)
    {
        $this->duongDan = $duongDan
            ?? dirname(__DIR__, 2)
                . DIRECTORY_SEPARATOR
                . 'storage'
                . DIRECTORY_SEPARATOR
                . 'lien-he.jsonl';
    }

    /**
     * Đọc tất cả phiếu; mỗi dòng là một đối tượng JSON.
     *
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException
     * @throws JsonException
     */
    public function layDanhSach(): array
    {
        if (!is_file($this->duongDan)) {
            return [];
        }

        $tep = fopen($this->duongDan, 'rb');

        if ($tep === false) {
            throw new RuntimeException(
                'Không thể mở file dữ liệu liên hệ.'
            );
        }

        $daKhoa = false;

        try {
            if (!flock($tep, LOCK_SH)) {
                throw new RuntimeException(
                    'Không thể khóa file dữ liệu liên hệ.'
                );
            }

            $daKhoa = true;
            $noiDung = stream_get_contents($tep);

            if ($noiDung === false) {
                throw new RuntimeException(
                    'Không thể đọc dữ liệu liên hệ.'
                );
            }
        } finally {
            if ($daKhoa) {
                flock($tep, LOCK_UN);
            }

            fclose($tep);
        }

        $danhSach = [];
        $cacDong = preg_split('/\r\n|\r|\n/', $noiDung);

        if ($cacDong === false) {
            throw new RuntimeException(
                'Không thể phân tách dữ liệu liên hệ.'
            );
        }

        foreach ($cacDong as $dong) {
            if (trim($dong) === '') {
                continue;
            }

            $phieu = json_decode(
                $dong,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($phieu)) {
                throw new RuntimeException(
                    'Một dòng dữ liệu liên hệ không hợp lệ.'
                );
            }

            $danhSach[] = $phieu;
        }

        return $danhSach;
    }

    /**
     * Thêm một phiếu bằng cách ghi thêm đúng một dòng JSON.
     *
     * @param array<string, mixed> $phieu
     * @throws RuntimeException
     * @throws JsonException
     */
    public function them(array $phieu): void
    {
        $thuMuc = dirname($this->duongDan);

        if (
            !is_dir($thuMuc)
            && !mkdir($thuMuc, 0775, true)
            && !is_dir($thuMuc)
        ) {
            throw new RuntimeException(
                'Không thể tạo thư mục lưu liên hệ.'
            );
        }

        $json = json_encode(
            $phieu,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        );

        $noiDungGhi = $json . PHP_EOL;
        $tep = fopen($this->duongDan, 'ab');

        if ($tep === false) {
            throw new RuntimeException(
                'Không thể mở file để lưu liên hệ.'
            );
        }

        $daKhoa = false;

        try {
            if (!flock($tep, LOCK_EX)) {
                throw new RuntimeException(
                    'Không thể khóa file để lưu liên hệ.'
                );
            }

            $daKhoa = true;
            $doDai = strlen($noiDungGhi);
            $daGhi = 0;

            while ($daGhi < $doDai) {
                $soByte = fwrite(
                    $tep,
                    substr($noiDungGhi, $daGhi)
                );

                if ($soByte === false || $soByte === 0) {
                    throw new RuntimeException(
                        'Không thể ghi đầy đủ phiếu liên hệ.'
                    );
                }

                $daGhi += $soByte;
            }

            if (!fflush($tep)) {
                throw new RuntimeException(
                    'Không thể hoàn tất ghi dữ liệu liên hệ.'
                );
            }
        } finally {
            if ($daKhoa) {
                flock($tep, LOCK_UN);
            }

            fclose($tep);
        }
    }
}