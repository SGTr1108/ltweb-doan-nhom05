"use strict";

document.addEventListener("click", function (event) {
    const button = event.target.closest("[data-gio-hang-step]");

    if (!button || button.disabled) {
        return;
    }

    const form = button.closest(".gio-hang-quantity-form-v2");
    const input = form?.querySelector('input[name="so_luong"]');

    if (!form || !input) {
        return;
    }

    const min = Number(input.min || 1);
    const max = Number(input.max || Number.MAX_SAFE_INTEGER);
    const current = Number.parseInt(input.value, 10);

    const safeCurrent = Number.isFinite(current) ? current : min;
    const step = Number(button.dataset.gioHangStep);
    const next = Math.max(min, Math.min(max, safeCurrent + step));

    if (next === safeCurrent) {
        return;
    }

    input.value = String(next);
    form.requestSubmit();
});
// MINIPOS-GIO-HANG-CHANGE-V2
document.addEventListener("change", function (event) {
    const input = event.target;

    if (
        !(input instanceof HTMLInputElement) ||
        input.name !== "so_luong"
    ) {
        return;
    }

    const form = input.closest(".gio-hang-quantity-form-v2");

    if (form && input.checkValidity()) {
        form.requestSubmit();
    }
});