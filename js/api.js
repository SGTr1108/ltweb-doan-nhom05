// api.js
// Cung cấp hàm dùng chung để tải dữ liệu JSON bằng fetch.

"use strict";

async function taiJSON(url) {
    const res = await fetch(url);

    if (!res.ok) {
        throw new Error(`Không thể tải dữ liệu: ${res.status}`);
    }

    return await res.json();
}

export { taiJSON };