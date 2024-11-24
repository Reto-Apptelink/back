const BASE_URL = `${window.location.origin}`;
const API_BASE_URL = `${BASE_URL}/api-iims/digitales/v1`;

async function fetchDataFromApi(url, data = {}, method = 'GET', headers = {}) {
    // const queryString = new URLSearchParams(queryParams).toString();
    // const fullUrl = `${API_BASE_URL}/${url}?${queryString}`;
    const fullUrl = method === 'GET' ? `${API_BASE_URL}/${url}?${new URLSearchParams(data)}` : `${API_BASE_URL}/${url}`;

    try {
        const response = await fetch(fullUrl, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                ...headers
            },
            body: method !== 'GET' ? JSON.stringify(data) : null
        });
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al obtener los datos del servidor');
        }
        const result = await response.json();
        return result;
    } catch (error) {
        console.error(error);
        return null;
    }
}