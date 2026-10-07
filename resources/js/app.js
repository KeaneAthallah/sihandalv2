import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

Alpine.store('toast', {
    toasts: [],
    show(message, type = 'info') {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        }, 5000);
    },
    success(message) {
        this.show(message, 'success');
    },
    error(message) {
        this.show(message, 'error');
    },
    warning(message) {
        this.show(message, 'warning');
    },
    info(message) {
        this.show(message, 'info');
    },
});

Alpine.store('modal', {
    active: null,
    open(name) {
        this.active = name;
    },
    close() {
        this.active = null;
    },
});

Alpine.start();

// Kalender untuk seluruh input tanggal (class "datepicker"),
// termasuk baris BKU yang ditambahkan secara dinamis via Alpine.
const initDatepickers = (root) => {
    (root || document)
        .querySelectorAll('input.datepicker:not([data-flatpickr-initialized])')
        .forEach((el) => {
            el.setAttribute('data-flatpickr-initialized', '1');
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                locale: Indonesian,
            });
        });
};

initDatepickers();

// Baris dinamis (mis. BKU) tidak ada saat DOM ready, jadi
// inisialisasi ulang setiap ada elemen baru yang masuk ke DOM.
const datepickerObserver = new MutationObserver(() => initDatepickers());
datepickerObserver.observe(document.body, { childList: true, subtree: true });
