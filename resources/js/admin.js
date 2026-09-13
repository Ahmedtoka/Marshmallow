import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Sortable from 'sortablejs';

window.Alpine = Alpine;
window.Chart = Chart;
window.Sortable = Sortable;

Chart.defaults.font.family = "'Nunito', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.color = '#6B6890';
Chart.defaults.borderColor = '#E7E5F2';
Chart.defaults.plugins.legend.labels.usePointStyle = true;

/** Shared helper for fetch() calls that need the CSRF token. */
window.csrfFetch = (url, options = {}) =>
    fetch(url, {
        ...options,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
    });

/** Make any list sortable: <ul data-sortable="{{ route(...) }}"> with <li data-id="..."> children. POSTs {ids: [...]}. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable]').forEach((list) => {
        Sortable.create(list, {
            handle: '[data-handle]',
            animation: 150,
            onEnd: () => {
                const ids = [...list.querySelectorAll(':scope > [data-id]')].map((el) => el.dataset.id);
                window.csrfFetch(list.dataset.sortable, { method: 'POST', body: JSON.stringify({ ids }) });
            },
        });
    });
});

Alpine.start();
