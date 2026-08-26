import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

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
