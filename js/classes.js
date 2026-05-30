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
        try {
            const params = new URLSearchParams(new FormData(filterForm));
            const response = await fetch(`api/classes.php?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            // Se o servidor devolver um erro (ex: 404 ou 500)
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const payload = await response.json();
            resultsContainer.innerHTML = payload.html;

            if (resultsCount) {
                resultsCount.textContent = `${payload.count} class${payload.count === 1 ? '' : 'es'} found`;
            }
            
        } catch (error) {
            // 1. Mostrar erro na consola para debug
            console.error('Failed to fetch classes:', error);
            
            // 2. Mostrar mensagem visual amigável ao utilizador (usando as tuas classes CSS)
            resultsContainer.innerHTML = `
                <div class="glass-panel" style="text-align: center; padding: 3rem; grid-column: 1 / -1;">
                    <h3>Oops! Não foi possível carregar as aulas.</h3>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">Ocorreu um erro de ligação. Por favor, verifica a tua internet e tenta novamente.</p>
                </div>
            `;
            
            // 3. Atualizar o contador para refletir o erro
            if (resultsCount) {
                resultsCount.textContent = 'Erro ao carregar';
            }
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