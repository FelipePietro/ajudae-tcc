const methods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];
const credentialHeaders = ['Authorization', 'Proxy-Authorization', 'Cookie', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN'];
const statusMessages = {
    400: 'A solicitação contém dados inválidos.',
    401: 'Autenticação necessária. Entre novamente.',
    403: 'Você não tem permissão para esta operação.',
    404: 'O recurso solicitado não está disponível.',
    409: 'O estado do registro mudou. Atualize os dados antes de tentar novamente.',
    419: 'A página expirou. Atualize a página e tente novamente.',
    422: 'Revise os campos informados.',
    429: 'Muitas tentativas. Aguarde antes de tentar novamente.',
};

export class ApiClientError extends Error {
    constructor(kind, message, { status = 0, errors = {}, retryAfter = null } = {}) {
        super(message);
        this.name = 'ApiClientError';
        this.kind = kind;
        this.status = status;
        this.errors = errors;
        this.retryAfter = retryAfter;
    }
}

function configurationError(message) {
    return new ApiClientError('configuration', message);
}

function parseUrl(address, base) {
    let url;
    try {
        url = new URL(address, base);
    } catch {
        throw configurationError('Informe um endereço HTTP válido.');
    }
    if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password || url.hash) {
        throw configurationError('Informe um endereço HTTP sem credenciais nem fragmento.');
    }
    return url;
}

function validateTimeout(value) {
    if (!Number.isInteger(value) || value < 1 || value > 2_147_483_647) {
        throw configurationError('Informe um timeout válido em milissegundos.');
    }
}

function requestBody(body, headers) {
    if (body === undefined || body instanceof FormData) {
        headers.delete('Content-Type');
        return body;
    }
    let json;
    try {
        json = JSON.stringify(body);
    } catch {
        throw configurationError('O corpo precisa ser serializável em JSON ou FormData.');
    }
    if (json === undefined) throw configurationError('O corpo precisa ser serializável em JSON ou FormData.');
    headers.set('Content-Type', 'application/json');
    return json;
}

function requireToken(token, label) {
    if (typeof token !== 'string' || !token || /\s/.test(token)) {
        throw configurationError(`Informe um token ${label} válido para este contexto.`);
    }
    return token;
}

async function readResponse(response, method) {
    if (response.ok && (method === 'HEAD' || [204, 205].includes(response.status))) return null;

    const content = await response.text();
    const type = response.headers.get('Content-Type') ?? '';
    let parsed;
    if (content && /^application\/(?:json|[\w.-]+\+json)(?:\s*;|\s*$)/i.test(type)) {
        try {
            parsed = JSON.parse(content);
        } catch {
            // The HTTP status still applies if the error body cannot be parsed.
        }
    }
    if (!response.ok) {
        const status = response.status;
        const useMessage = [400, 409, 422].includes(status) && typeof parsed?.message === 'string' && parsed.message.trim();
        const message = useMessage ? parsed.message : statusMessages[status] ?? 'O servidor não conseguiu concluir a operação.';
        const fields = parsed?.errors;
        const errors = status === 422 && fields && typeof fields === 'object' && !Array.isArray(fields) ? fields : {};
        throw new ApiClientError('http', message, { status, errors, retryAfter: response.headers.get('Retry-After') });
    }
    if (!content) return null;
    if (parsed !== undefined) return parsed;
    throw new ApiClientError('invalid-response', 'O servidor retornou uma resposta inválida.', { status: response.status });
}

export function createApiClient({
    baseUrl,
    mode = 'public',
    getBearerToken,
    getCsrfToken = () => globalThis.document?.querySelector('meta[name="csrf-token"]')?.content,
    timeoutMs = 15_000,
    fetchImpl = globalThis.fetch,
    pageOrigin,
} = {}) {
    const origin = globalThis.location?.origin ?? pageOrigin;
    if (typeof baseUrl !== 'string' || !baseUrl.trim()) throw configurationError('Informe a URL base.');
    const base = parseUrl(baseUrl, origin);
    if (base.search || /%2f|%5c/i.test(base.pathname)) throw configurationError('Informe uma URL base sem parâmetros.');
    base.pathname = base.pathname.replace(/\/?$/, '/');
    if (!['public', 'session', 'bearer'].includes(mode)) throw configurationError('Modo de autenticação inválido.');
    if (mode === 'session' && base.origin !== origin) throw configurationError('Sessão Web exige a origem da página.');
    if (mode === 'bearer' && typeof getBearerToken !== 'function') throw configurationError('Forneça o token Bearer por função.');
    if (mode === 'session' && typeof getCsrfToken !== 'function') throw configurationError('Forneça o token CSRF por função.');
    if (typeof fetchImpl !== 'function') throw configurationError('O cliente exige uma implementação de fetch.');
    validateTimeout(timeoutMs);
    const activeWrites = new Set();

    async function request(path, { method = 'GET', body, headers: extraHeaders, signal, timeoutMs: limit = timeoutMs, dedupeKey } = {}) {
        if (typeof path !== 'string') throw configurationError('Informe o caminho da operação.');
        const url = parseUrl(path, base);
        const pathInBase = url.pathname === base.pathname.slice(0, -1) || url.pathname.startsWith(base.pathname);
        if (url.origin !== base.origin || !pathInBase || /%2f|%5c/i.test(url.pathname)) {
            throw configurationError('O endereço precisa pertencer à URL base do cliente.');
        }
        const verb = typeof method === 'string' ? method.toUpperCase() : '';
        if (!methods.includes(verb)) throw configurationError('Método HTTP não suportado.');
        const reading = verb === 'GET' || verb === 'HEAD';
        if (reading && body !== undefined) throw configurationError('Requisições de leitura não recebem corpo.');
        validateTimeout(limit);
        if (dedupeKey !== undefined && (typeof dedupeKey !== 'string' || !dedupeKey.trim())) {
            throw configurationError('Informe uma chave de envio válida.');
        }
        const key = !reading && dedupeKey !== undefined ? `${verb} ${url.href} ${dedupeKey}` : null;
        if (activeWrites.has(key)) throw new ApiClientError('duplicate', 'Esta operação já está em andamento.');

        const headers = new Headers(extraHeaders);
        if (credentialHeaders.some(header => headers.has(header))) {
            throw configurationError('Configure as credenciais no modo de autenticação.');
        }
        headers.set('Accept', 'application/json');
        if (mode === 'bearer') headers.set('Authorization', `Bearer ${requireToken(getBearerToken(), 'Bearer')}`);
        if (mode === 'session' && !reading) headers.set('X-CSRF-TOKEN', requireToken(getCsrfToken(), 'CSRF'));
        const payload = requestBody(body, headers);

        const controller = new AbortController();
        const cancel = () => controller.abort(new ApiClientError('cancelled', 'Requisição cancelada.'));
        if (signal?.aborted) cancel();
        else signal?.addEventListener('abort', cancel, { once: true });
        const timer = setTimeout(() => controller.abort(new ApiClientError('timeout', 'O servidor demorou para responder.')), limit);
        if (key) activeWrites.add(key);

        try {
            controller.signal.throwIfAborted();
            const response = await fetchImpl(url, {
                method: verb,
                headers,
                body: payload,
                signal: controller.signal,
                credentials: mode === 'session' ? 'same-origin' : 'omit',
                cache: 'no-store',
                redirect: 'error',
            });
            const result = await readResponse(response, verb);
            controller.signal.throwIfAborted();
            return result;
        } catch (error) {
            controller.signal.throwIfAborted();
            if (error instanceof ApiClientError) throw error;
            throw new ApiClientError('network', 'Não foi possível conectar ao servidor.');
        } finally {
            clearTimeout(timer);
            signal?.removeEventListener('abort', cancel);
            if (key) activeWrites.delete(key);
        }
    }

    return { request };
}
