import { publishNotice } from './notifications-demo.js';
import { closureProgress, readHistory } from './participation-demo.js';

// Synthetic records only. The public projection contains no participant IDs or private history.
export const certificateKey = fixture => `ajudae.certificados.v1.${fixture.storageId ?? fixture.id}`;
export function certificateEligibility(fixture, history, personId) {
    const progress = closureProgress(fixture, history.filter(entry => entry.eventId === undefined || entry.eventId === fixture.id), Date.now());
    const rows = progress.agendas.filter(a => a.state === 'finalizada').flatMap(a => {
        const record = a.records.find(r => r.personId === personId && r.selection === 'aprovada' && r.presence === 'presente' && !r.withdrawal);
        return record && Number.isInteger(a.minutes) && a.minutes >= 30 ? [{ agendaId: a.id, minutes: a.minutes, student: record.student, monitoring: record.monitoring }] : [];
    });
    const minutes = rows.reduce((n, r) => n + r.minutes, 0);
    const reason = progress.status !== 'finalizado' ? 'Aguarde a finalização do Evento pelo responsável.'
        : !minutes ? 'Não há presença confirmada em Agenda válida concluída neste Evento.' : '';
    return { minutes, rows, reason, allowed: !reason, studentAllowed: !reason && rows.some(r => r.student) && rows.every(r => !r.student || r.monitoring === 'validada') };
}

export function issueCertificate(fixture, history, personId, modality, existing, code, now) {
    if (existing) return existing.personId === personId && existing.eventId === fixture.id ? existing : null;
    const result = certificateEligibility(fixture, history, personId);
    if (!result.allowed || !['geral', 'estudantil'].includes(modality) || (modality === 'estudantil' && !result.studentAllowed)) return null;
    if (!/^AJD-[A-F0-9]{32}$/.test(code) || !Number.isFinite(now)) return null;
    const person = fixture.records.find(r => r.personId === personId);
    if (!person) return null;
    return { personId, eventId: fixture.id, name: person.name, title: fixture.title, issuer: fixture.certificateIssuer,
        minutes: result.minutes, modality, code, issuedAt: now, status: 'valido' };
}

export function publicCertificate(record) {
    return Object.fromEntries(['name', 'title', 'issuer', 'minutes', 'modality', 'code', 'issuedAt', 'status'].map(k => [k, record[k]]));
}

export const durationLabel = minutes => `${Math.floor(minutes / 60)} h${minutes % 60 ? ` ${minutes % 60} min` : ''}`;
const dateLabel = time => new Intl.DateTimeFormat('pt-BR', { timeZone: 'America/Sao_Paulo' }).format(time);

// A real single-page PDF, WinAnsi encoded with byte offsets. No remote fonts or document services.
export function certificatePdf(record) {
    const quote = text => String(text).replace(/[^\x20-\xFF]/g, '-').replace(/[\\()]/g, '\\$&');
    const lines = [
        [32, 'AJUDAE'], [24, 'CERTIFICADO DE PARTICIPAÇÃO'], [12, 'Certificamos que'],
        [20, record.name], [12, 'participou do Evento'], [17, record.title],
        [16, `Carga horária: ${durationLabel(record.minutes)} (${record.minutes} minutos)`],
        [12, `Modalidade: ${record.modality === 'estudantil' ? 'Estudantil - monitoria confirmada' : 'Geral'}`],
        [12, `Emissor: ${record.issuer}`], [12, `Emitido em: ${dateLabel(record.issuedAt)}`],
        [10, `Código: ${record.code}`], [10, 'Consulte a autenticidade na página Verificar certificado do Ajudae.'],
    ];
    let y = 525;
    const stream = '0.09 0.25 0.18 rg\n' + lines.map(([size, line]) => {
        // Fixed synthetic names/titles fit the page; long lines wrap without truncation.
        const words = line.split(' '); const wrapped = []; let current = '';
        const limit = Math.floor(680 / (size * 0.58));
        for (const word of words) { if ((current + ' ' + word).trim().length > limit) { wrapped.push(current); current = word; } else current = (current + ' ' + word).trim(); }
        wrapped.push(current);
        return wrapped.map(value => { const command = `BT /F1 ${size} Tf 70 ${y} Td (${quote(value)}) Tj ET`; y -= size + 14; return command; }).join('\n');
    }).join('\n');
    const objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>', `<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`,
        '<< /Title (Ajudae - Certificado de participa\xE7\xE3o) /Author (Ajudae) >>'];
    let pdf = '%PDF-1.4\n'; const offsets = [0];
    objects.forEach((object, i) => { offsets.push(pdf.length); pdf += `${i + 1} 0 obj\n${object}\nendobj\n`; });
    const start = pdf.length;
    pdf += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n${offsets.slice(1).map(n => `${String(n).padStart(10, '0')} 00000 n \n`).join('')}trailer\n<< /Size ${objects.length + 1} /Root 1 0 R /Info 6 0 R >>\nstartxref\n${start}\n%%EOF`;
    return Uint8Array.from(pdf, c => c.charCodeAt(0));
}

function showRecord(root, record) {
    root.querySelector('[data-certificate-result]').hidden = false;
    for (const [key, value] of Object.entries({ name: record.name, title: record.title, issuer: record.issuer, duration: durationLabel(record.minutes), date: dateLabel(record.issuedAt), code: record.code, modality: record.modality === 'estudantil' ? 'Estudantil · monitoria confirmada' : 'Geral' })) {
        root.querySelectorAll(`[data-certificate-${key}]`).forEach(el => { el.textContent = value; });
    }
}

function init(root) {
    const fixture = JSON.parse(root.querySelector('[data-certificate-fixture]').textContent);
    const manager = root.dataset.certificates === 'management';
    let blobUrl;
    const render = () => {
        try {
            const history = readHistory(sessionStorage, fixture.storageId ?? fixture.id);
            if (manager) {
                const list = root.querySelector('[data-certificate-eligibility]'); list.replaceChildren();
                for (const personId of new Set(fixture.records.map(r => r.personId))) {
                    const person = fixture.records.find(r => r.personId === personId);
                    const result = certificateEligibility(fixture, history, personId);
                    const row = document.createElement('p'); row.className = 'mt-3';
                    row.textContent = `${person.name}: ${result.reason || `${durationLabel(result.minutes)} · certificado geral disponível`}${result.allowed && person.student && !result.studentAllowed ? ' · Monitoria pendente: modalidade estudantil indisponível.' : ''}`;
                    list.append(row);
                }
                return;
            }
            const personId = fixture.actor.id;
            const records = JSON.parse(sessionStorage.getItem(certificateKey(fixture)) ?? '{}');
            const record = records[`${personId}:${fixture.id}`];
            const eligibility = certificateEligibility(fixture, history, personId);
            root.querySelector('[data-certificate-reason]').textContent = record ? 'Certificado disponível. A carga horária e o código são preservados.' : eligibility.reason || `Carga horária válida: ${durationLabel(eligibility.minutes)}. Escolha a modalidade antes de gerar seu certificado.`;
            root.querySelector('[data-certificate-issue]').disabled = Boolean(record) || !eligibility.allowed;
            root.querySelector('[value="estudantil"]').disabled = !eligibility.studentAllowed;
            root.querySelector('[data-certificate-monitoring]').hidden = !eligibility.allowed || eligibility.studentAllowed;
            root.querySelector('[data-certificate-form]').hidden = Boolean(record);
            if (!record) return;
            showRecord(root, record);
            const link = root.querySelector('[data-certificate-verify]');
            link.href = `${root.dataset.verifyUrl}?codigo=${encodeURIComponent(record.code)}&contexto=visitante`;
            if (blobUrl) URL.revokeObjectURL(blobUrl);
            blobUrl = URL.createObjectURL(new Blob([certificatePdf(record)], { type: 'application/pdf' }));
            root.querySelector('[data-certificate-download]').href = blobUrl;
            root.querySelector('[data-certificate-download]').download = `certificado-${record.code}.pdf`;
            root.querySelector('[data-certificate-pdf]').href = blobUrl;
        } catch {
            root.querySelector('[data-certificate-error]').hidden = false;
            root.querySelector('[data-certificate-issue]')?.setAttribute('disabled', '');
        }
    };
    root.querySelector('[data-certificate-form]')?.addEventListener('submit', event => {
        event.preventDefault();
        try {
            const records = JSON.parse(sessionStorage.getItem(certificateKey(fixture)) ?? '{}');
            const key = `${fixture.actor.id}:${fixture.id}`;
            const code = 'AJD-' + Array.from(crypto.getRandomValues(new Uint8Array(16)), b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
            const record = issueCertificate(fixture, readHistory(sessionStorage, fixture.storageId ?? fixture.id), fixture.actor.id,
                root.querySelector('[name=modalidade]').value, records[key], code, Date.now());
            if (!record) return render();
            // One atomic write freezes the synthetic snapshot. Verification renders only publicCertificate().
            sessionStorage.setItem(certificateKey(fixture), JSON.stringify({ ...records, [key]: record }));
            publishNotice('certificado',certificateKey(fixture)+':'+key,[(new URL(location.href).searchParams.get('contexto')||'pessoa')+':1'],{event:fixture.id,example:new URL(location.href).searchParams.get('exemplo')});
            render();
            root.querySelector('[data-certificate-result]').focus();
        } catch { root.querySelector('[data-certificate-error]').hidden = false; }
    });
    const dialog = root.querySelector('[data-certificate-dialog]');
    const preview = root.querySelector('[data-certificate-preview]');
    preview?.addEventListener('click', () => dialog.showModal());
    root.querySelector('[data-certificate-close]')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('close', () => preview.focus());
    window.addEventListener('pageshow', render);
    document.addEventListener('participation:changed', render);
    render();
}

function verify(root) {
    const form = root.querySelector('form');
    const input = form.querySelector('input');
    const render = () => {
        root.querySelector('[data-certificate-result]').hidden = true;
        root.querySelector('[data-verification-message]').textContent = 'Não foi possível confirmar este certificado. Confira o código informado.';
        const code = input.value.trim().toUpperCase();
        if (!/^AJD-[A-F0-9]{32}$/.test(code)) return;
        try {
            // No fixture or private record is rendered on this public page.
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (!key.startsWith('ajudae.certificados.v1.')) continue;
                const records = Object.values(JSON.parse(sessionStorage.getItem(key)));
                const record = records.find(r => r.code === code && r.status === 'valido');
                if (record) { showRecord(root, publicCertificate(record)); root.querySelector('[data-verification-message]').textContent = 'Certificado válido.'; return; }
            }
        } catch { /* Neutral failure without disclosing storage details. */ }
    };
    input.value = new URL(location.href).searchParams.get('codigo') ?? '';
    form.addEventListener('submit', event => { event.preventDefault(); const url = new URL(location.href); url.searchParams.set('codigo', input.value.trim()); history.replaceState(null, '', url); render(); });
    if (input.value) render();
}

if (typeof document !== 'undefined') document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-certificates]').forEach(init);
    document.querySelectorAll('[data-certificate-verification]').forEach(verify);
});
