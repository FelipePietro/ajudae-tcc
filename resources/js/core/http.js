const messages = {
    0: 'Sem conexão com o servidor. Tente novamente.',
    401: 'Sua sessão terminou. Entre novamente.',
    403: 'Você não tem permissão para esta operação.',
    404: 'O recurso solicitado não está disponível.',
    409: 'O estado do registro mudou. Atualize a página antes de tentar novamente.',
    419: 'A página expirou. Atualize a página e tente novamente.',
    422: 'Revise os campos informados.',
    429: 'Muitas tentativas. Aguarde antes de tentar novamente.',
    500: 'O servidor não conseguiu concluir a operação. Tente novamente.',
};

export class HttpError extends Error {
    constructor(status, payload = null) {
        const details = status < 500 && ![401, 403, 404, 419, 429].includes(status)
            ? payload?.message
            : null;

        super(typeof details === 'string' && details.trim() !== ''
            ? details
            : messages[status] ?? messages[500]);
        this.name = 'HttpError';
        this.status = status;
        this.errors = status === 422 && payload?.errors ? payload.errors : {};
    }
}

export async function requestJson(url, { method = 'GET', body, signal, csrfToken, idempotencyKey } = {}) {
    const destination = new URL(url, window.location.href);
    if (destination.origin !== window.location.origin || destination.username || destination.password) {
        throw new TypeError('O endereço deve pertencer à origem da página.');
    }

    const verb = method.toUpperCase();
    const headers = new Headers({ Accept: 'application/json' });
    if (!['GET', 'HEAD', 'OPTIONS'].includes(verb)) {
        const token = csrfToken ?? document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            throw new HttpError(419);
        }
        headers.set('X-CSRF-TOKEN', token);
        if (idempotencyKey !== undefined) {
            headers.set('Idempotency-Key', idempotencyKey);
        }
    }

    let payload = body;
    if (body !== undefined && !(body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
        payload = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(destination, {
            method: verb,
            headers,
            body: payload,
            signal,
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'error',
        });
    } catch (error) {
        if (error.name === 'AbortError') throw error;
        throw new HttpError(0);
    }

    if (response.status === 204 || (response.ok && verb === 'HEAD')) return null;

    let result = null;
    const type = response.headers.get('Content-Type') ?? '';
    if (/^application\/(?:json|[\w.-]+\+json)\b/i.test(type)) {
        try {
            result = await response.json();
        } catch (error) {
            if (error.name === 'AbortError') throw error;
        }
    }

    if (!response.ok) throw new HttpError(response.status, result);
    if (!result || typeof result !== 'object') throw new HttpError(500);
    return result;
}
