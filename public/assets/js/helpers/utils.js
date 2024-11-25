let isFetching = false;
let perPage = 10;
let currentPage = 1;

function pagination(pagination, containerId, pageInfoId, prevClass, nextClass, callback) {
    const contenedorPaginacion = document.getElementById(containerId);
    const totalCount = pagination.total;
    const currentPage = pagination.current_page;
    const perPage = pagination.per_page;

    // Calcular el rango de registros que se están mostrando
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(start + perPage - 1, totalCount);

    const pageInfo = `Mostrando ${start} - ${end} de ${totalCount} registros`;
    document.getElementById(pageInfoId).textContent = pageInfo;

    let html = '';
    if (pagination.last_page > 1) {
        html += `
            <button class="btn btn-sm ${prevClass} page-link" ${!pagination.prev_page_url ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>
        `;
        html += `
            <span class="mx-2">Página ${pagination.current_page} de ${pagination.last_page}</span>
        `;
        html += `
            <button class="btn btn-sm ${nextClass} page-link" ${!pagination.next_page_url ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>
        `;
    }

    contenedorPaginacion.innerHTML = html;

    // Agregar eventos para los botones de paginación
    document.querySelector(`.${prevClass}`)?.addEventListener('click', () => {
        if (pagination.prev_page_url) {
            callback(pagination.current_page - 1);
        }
    });

    document.querySelector(`.${nextClass}`)?.addEventListener('click', () => {
        if (pagination.next_page_url) {
            callback(pagination.current_page + 1);
        }
    });
}