import assert from 'node:assert/strict';
import test from 'node:test';
import { ApiClientError, createApiClient } from '../../resources/js/core/api-client.js';

const baseUrl = 'https://app.example.test/api/v1';
const json = (data, status = 200, headers = {}) => Response.json(data, { status, headers });
const rejectsWith = (action, kind, status = 0) => assert.rejects(action, error => {
    assert.ok(error instanceof ApiClientError);
    assert.equal(error.kind, kind);
    assert.equal(error.status, status);
    return true;
});

test('retorna o envelope inteiro e aceita parâmetros de consulta', async () => {
    const body = { success: true, message: 'Consulta concluída.', data: { data: ['registro'], current_page: 2 } };
    const client = createApiClient({ baseUrl, fetchImpl: async (url, options) => {
        assert.equal(url.href, `${baseUrl}/eventos?page=2`);
        assert.equal(options.method, 'GET');
        assert.equal(options.headers.get('Accept'), 'application/json');
        assert.equal(options.credentials, 'omit');
        assert.equal(options.headers.has('Authorization'), false);
        assert.equal(options.redirect, 'error');
        return json(body);
    } });

    assert.deepEqual(await client.request('eventos?page=2'), body);
});

for (const method of ['POST', 'PUT', 'PATCH', 'DELETE']) {
    test(`${method} serializa JSON e usa Bearer fornecido em memória`, async () => {
        const client = createApiClient({
            baseUrl,
            mode: 'bearer',
            getBearerToken: () => 'token-sintetico',
            fetchImpl: async (_, options) => {
                assert.equal(options.method, method);
                assert.equal(options.credentials, 'omit');
                assert.equal(options.headers.get('Authorization'), 'Bearer token-sintetico');
                assert.equal(options.headers.get('Content-Type'), 'application/json');
                assert.equal(options.headers.has('X-CSRF-TOKEN'), false);
                assert.deepEqual(JSON.parse(options.body), { nota: 5 });
                return json({ success: true }, 201);
            },
        });
        assert.deepEqual(await client.request('eventos/1', { method, body: { nota: 5 } }), { success: true });
    });
}

test('FormData mantém o boundary sob controle do fetch e Web envia CSRF', async () => {
    const data = new FormData();
    data.append('arquivo', new Blob(['amostra sintética']), 'amostra.txt');
    const client = createApiClient({
        baseUrl: '/conta',
        pageOrigin: 'https://app.example.test',
        mode: 'session',
        getCsrfToken: () => 'csrf-sintetico',
        fetchImpl: async (url, options) => {
            assert.equal(url.pathname, '/conta/documentos');
            assert.equal(options.body, data);
            assert.equal(options.credentials, 'same-origin');
            assert.equal(options.headers.get('X-CSRF-TOKEN'), 'csrf-sintetico');
            assert.equal(options.headers.get('Accept-Language'), 'pt-BR');
            assert.equal(options.headers.has('Authorization'), false);
            assert.equal(options.headers.has('Content-Type'), false);
            return new Response(null, { status: 204 });
        },
    });

    assert.equal(await client.request('documentos', {
        method: 'POST', body: data, headers: { 'Accept-Language': 'pt-BR', 'Content-Type': 'multipart/form-data' },
    }), null);
});

test('sessão, Bearer e público mantêm credenciais segregadas', async () => {
    const client = createApiClient({ baseUrl, fetchImpl: async () => { assert.fail('Nenhuma requisição deve sair.'); } });
    for (const header of ['Authorization', 'Cookie', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN']) {
        await rejectsWith(client.request('eventos', { headers: { [header]: 'sintetico' } }), 'configuration');
    }
    for (const path of ['https://other.example.test/eventos', '/login', '../fora']) {
        await rejectsWith(client.request(path), 'configuration');
    }
    assert.throws(() => createApiClient({ baseUrl, mode: 'bearer' }), { kind: 'configuration' });
    assert.throws(() => createApiClient({ baseUrl, mode: 'session', pageOrigin: 'https://other.example.test' }), { kind: 'configuration' });
    const session = createApiClient({ baseUrl: 'https://app.example.test', mode: 'session', pageOrigin: 'https://app.example.test' });
    await rejectsWith(session.request('conta/senha', { method: 'PATCH' }), 'configuration');
});

test('sessão consulta sua rota base sem enviar CSRF na leitura', async () => {
    const client = createApiClient({
        baseUrl: '/conta',
        pageOrigin: 'https://app.example.test',
        mode: 'session',
        getCsrfToken: () => { assert.fail('GET não deve consultar o token CSRF.'); },
        fetchImpl: async (url, options) => {
            assert.equal(url.pathname, '/conta');
            assert.equal(options.credentials, 'same-origin');
            assert.equal(options.headers.has('X-CSRF-TOKEN'), false);
            return json({ success: true });
        },
    });
    assert.deepEqual(await client.request('/conta'), { success: true });
});

test('cada requisição Bearer consulta o token vigente', async () => {
    let token = 'sintetico-a';
    const used = [];
    const client = createApiClient({ baseUrl, mode: 'bearer', getBearerToken: () => token, fetchImpl: async (_, options) => {
        used.push(options.headers.get('Authorization'));
        return new Response(null, { status: 204 });
    } });
    await client.request('pessoas/me');
    token = 'sintetico-b';
    await client.request('pessoas/me');
    assert.deepEqual(used, ['Bearer sintetico-a', 'Bearer sintetico-b']);
});

test('retorna null para resposta vazia e respeita JSON com sufixo +json', async () => {
    const responses = [new Response(null, { status: 204 }), new Response(null, { status: 205 }), new Response(''),
        new Response('false', { headers: { 'Content-Type': 'application/problem+json; charset=utf-8' } })];
    const client = createApiClient({ baseUrl, fetchImpl: async () => responses.shift() });
    for (const value of [null, null, null, false]) assert.equal(await client.request('eventos'), value);
});

for (const status of [400, 401, 403, 404, 409, 419, 422, 429, 500]) {
    test(`normaliza HTTP ${status} sem confundir com falha de rede`, async () => {
        const fields = { nota: ['A nota deve ser um inteiro.'] };
        const response = json({ success: false, message: 'Detalhe do servidor.', errors: fields }, status, { 'Retry-After': '10' });
        const client = createApiClient({ baseUrl, fetchImpl: async () => response });
        await assert.rejects(client.request('eventos'), error => {
            assert.ok(error instanceof ApiClientError);
            assert.equal(error.kind, 'http');
            assert.equal(error.status, status);
            assert.deepEqual(error.errors, status === 422 ? fields : {});
            assert.equal(error.retryAfter, '10');
            if ([400, 409, 422].includes(status)) assert.equal(error.message, 'Detalhe do servidor.');
            if (status === 500) assert.ok(!error.message.includes('Detalhe do servidor'));
            return true;
        });
    });
}

test('preserva status quando erro HTTP tem HTML e identifica sucesso inválido', async () => {
    const responses = [new Response('<html/>', { status: 502 }), new Response('<html/>', { status: 200 }),
        new Response('{', { headers: { 'Content-Type': 'application/json' } })];
    const client = createApiClient({ baseUrl, fetchImpl: async () => responses.shift() });
    await rejectsWith(client.request('eventos'), 'http', 502);
    await rejectsWith(client.request('eventos'), 'invalid-response', 200);
    await rejectsWith(client.request('eventos'), 'invalid-response', 200);
});

test('normaliza falha de rede sem repassar detalhes internos', async () => {
    const client = createApiClient({ baseUrl, fetchImpl: async () => { throw new Error('Detalhe interno.'); } });
    await rejectsWith(client.request('eventos'), 'network');
});

const waitForAbort = (_, { signal }) => new Promise((resolve, reject) => {
    signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')), { once: true });
});

test('timeout aborta a requisição pelo sinal interno', async () => {
    const client = createApiClient({ baseUrl, fetchImpl: waitForAbort, timeoutMs: 200 });
    await rejectsWith(client.request('eventos', { timeoutMs: 20 }), 'timeout');
});

test('cancelamento externo aborta requisições em curso e evita envio pré-cancelado', async () => {
    let attempts = 0;
    const client = createApiClient({ baseUrl, fetchImpl: (...args) => { attempts++; return waitForAbort(...args); } });
    const controller = new AbortController();
    const inFlight = client.request('eventos', { signal: controller.signal });
    controller.abort('Motivo externo');
    await rejectsWith(inFlight, 'cancelled');
    await rejectsWith(client.request('eventos', { signal: controller.signal }), 'cancelled');
    assert.equal(attempts, 1);
});

test('timeout também abrange a leitura do corpo após os headers', async () => {
    const client = createApiClient({ baseUrl, timeoutMs: 20, fetchImpl: async (_, { signal }) => new Response(new ReadableStream({
        start(stream) {
            signal.addEventListener('abort', () => stream.error(new DOMException('Aborted', 'AbortError')), { once: true });
        },
    })) });
    await rejectsWith(client.request('eventos'), 'timeout');
});

test('chave impede envio concorrente e fica disponível após erro e conclusão', async () => {
    const pending = [];
    const client = createApiClient({ baseUrl, fetchImpl: () => new Promise(resolve => { pending.push(resolve); }) });
    const options = { method: 'POST', dedupeKey: 'candidatura-1', body: { agenda_id: 1 } };
    const first = client.request('agendas/1/inscricoes', options);
    await rejectsWith(client.request('agendas/1/inscricoes', options), 'duplicate');
    assert.equal(pending.length, 1);
    pending.shift()(json({ message: 'Conflito.' }, 409));
    await rejectsWith(first, 'http', 409);
    for (let attempt = 0; attempt < 2; attempt++) {
        const retry = client.request('agendas/1/inscricoes', options);
        pending.shift()(json({ success: true }, 201));
        assert.deepEqual(await retry, { success: true });
    }
});
