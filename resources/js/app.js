import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

const applyTheme = (mode) => {
    const isDark = mode === 'dark';
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

const resolvedTheme = (mode) =>
    mode === 'system'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : mode;

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

Alpine.store('theme', {
    mode: localStorage.getItem('theme') || 'system',

    get resolved() {
        return resolvedTheme(this.mode);
    },

    apply() {
        applyTheme(this.resolved);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { mode: this.resolved } }));
    },

    set(mode) {
        this.mode = mode;
        localStorage.setItem('theme', mode);
        this.apply();
    },

    cycle() {
        const order = ['light', 'dark', 'system'];
        this.set(order[(order.indexOf(this.mode) + 1) % order.length]);
    },

    init() {
        if (this.initialized) {
            return;
        }
        this.initialized = true;
        this.apply();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.mode === 'system') {
                this.apply();
            }
        });
    },
});

Alpine.start();

Alpine.store('theme').init();

// Helper for chart / picker consumers that need the resolved mode.
window.currentTheme = () => (document.documentElement.classList.contains('dark') ? 'dark' : 'light');

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

    syncDatepickerTheme();
};

// Flatpickr dimuat sebagai overlay di body, jadi kelas tema harus
// disetel ulang setiap kali tema berubah.
const syncDatepickerTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('.flatpickr-calendar').forEach((cal) => {
        cal.classList.toggle('dark', isDark);
    });
};

window.addEventListener('theme-changed', () => syncDatepickerTheme());

initDatepickers();

// Baris dinamis (mis. BKU) tidak ada saat DOM ready, jadi
// inisialisasi ulang setiap ada elemen baru yang masuk ke DOM.
const datepickerObserver = new MutationObserver(() => initDatepickers());
datepickerObserver.observe(document.body, { childList: true, subtree: true });
