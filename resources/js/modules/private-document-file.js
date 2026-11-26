export function validatePrivateDocument(file) {
    if (!file) return 'Selecione um arquivo.';
    if (file.size > 10 * 1024 * 1024) return 'O arquivo deve ter até 10 MiB.';
    if (!/\.(pdf|jpe?g|png)$/i.test(file.name)) return 'Selecione um arquivo PDF, JPEG ou PNG.';
    return '';
}
