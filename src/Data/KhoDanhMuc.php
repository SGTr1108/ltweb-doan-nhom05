<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

/** Lớp truy cập dữ liệu của danh mục: mọi câu SQL về danh_muc nằm ở đây. */
final class KhoDanhMuc
{
    public function __construct(private PDO $db) {}

    /**
     * Mọi danh mục kèm số sản phẩm (danh mục chưa có hàng → 0),
     * bằng một câu LEFT JOIN + GROUP BY.
     *
     * @return list<array{id:int, ten:string, slug:string, so_san_pham:int}>
     */
    public function tatCa(): array
    {
        $st = $this->db->query(
            'SELECT dm.id, dm.ten, dm.slug, COUNT(sp.id) AS so_san_pham
               FROM danh_muc dm
          LEFT JOIN san_pham sp ON sp.danh_muc_id = dm.id
           GROUP BY dm.id, dm.ten, dm.slug
           ORDER BY dm.ten'
        );

        return $st->fetchAll();
    }

    /** @return array{id:int, ten:string, slug:string}|null */
    public function timTheoId(int $id): ?array
    {
        $st = $this->db->prepare('SELECT id, ten, slug FROM danh_muc WHERE id = ?');
        $st->execute([$id]);
        $dong = $st->fetch();

        return $dong ?: null;
    }

    /** Danh mục có tồn tại không (kiểm tra trước khi thêm/sửa sản phẩm). */
    public function tonTai(int $id): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM danh_muc WHERE id = ?');
        $st->execute([$id]);

        return $st->fetchColumn() !== false;
    }
}
