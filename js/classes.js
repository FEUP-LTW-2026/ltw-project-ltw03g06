const filterForm = document.querySelector('[data-schedule-filters]');
const resultsContainer = document.querySelector('[data-schedule-results]');
const resultsCount = document.querySelector('[data-schedule-count]');

if (filterForm && resultsContainer) {
    const debounce = (callback, delay = 180) => {
        let timerId;

        return (...args) => {
            window.clearTimeout(timerId);
            timerId = window.setTimeout(() => callback(...args), delay);
        };
    };

    const refreshSchedule = async () => {
        const params = new URLSearchParams(new FormData(filterForm));
        const response = await fetch(`api/classes.php?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        resultsContainer.innerHTML = payload.html;

        if (resultsCount) {
            resultsCount.textContent = `${payload.count} class${payload.count === 1 ? '' : 'es'} found`;
        }
    };

    const debouncedRefresh = debounce(refreshSchedule, 220);

    filterForm.addEventListener('input', debouncedRefresh);
    filterForm.addEventListener('change', refreshSchedule);

    filterForm.addEventListener('submit', event => {
        event.preventDefault();
        refreshSchedule();
    });
}
