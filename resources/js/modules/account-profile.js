import { requestJson } from '../core/http.js';

/**
 * Módulo de integração para perfil privado de Pessoa e ONG.
 * Bloco 9 - João Pedro - Tarefa 1 e 2
 * 
 * Endpoints consumidos:
 * - GET /conta/pessoa/dados
 * - PATCH /conta/pessoa/{id}/dados
 * - POST /conta/pessoa/{id}/imagem
 * - DELETE /conta/pessoa/{id}/imagem
 * - GET /conta/ong/dados
 * - PATCH /conta/ong/{id}/dados
 * - POST /conta/ong/{id}/imagem
 * - DELETE /conta/ong/{id}/imagem
 */

function showMessage(element, text, type = 'info') {
    if (!element) return;
    element.textContent = text;
    element.hidden = !text;
    element.className = `account-feedback account-feedback--${type}`;
}

/**
 * Carrega dados da Pessoa via API e preenche o formulário
 */
async function loadPersonData(form, feedback) {
    showMessage(feedback, 'Carregando dados...', 'info');
    try {
        const { data } = await requestJson('/conta/pessoa/dados');
        if (data) {
            // Preenche os campos editáveis
            const editableFields = [
                'email_pessoa', 'tele_pessoa', 'cep_pessoa', 'logradouro_pessoa',
                'compl_pessoa', 'cidade_pessoa', 'bairro_pessoa', 'uf_pessoa'
            ];
            for (const field of editableFields) {
                const input = form.elements[field];
                if (input && data[field] !== undefined) {
                    input.value = data[field] || '';
                }
            }
            showMessage(feedback, '', 'info');
            applyStoredPhoto(form.closest('[data-person-profile-edit]'), data.pfp_pessoa_link);
            return data;
        }
    } catch (error) {
        showMessage(feedback, 'Não foi possível carregar os dados. Recarregue a página.', 'error');
    }
    return null;
}

function applyStoredPhoto(root, url) {
    if (!root || !url) return;
    const preview = root.querySelector('[data-photo-preview]');
    const placeholder = root.querySelector('[data-photo-placeholder]');
    const removeButton = root.querySelector('[data-photo-remove]');
    if (preview) {
        preview.src = url;
        preview.hidden = false;
    }
    if (placeholder) placeholder.hidden = true;
    if (removeButton) removeButton.hidden = false;
}

/**
 * Carrega dados da ONG via API e preenche o formulário
 */
async function loadOngData(form, feedback) {
    showMessage(feedback, 'Carregando dados...', 'info');
    try {
        const { data } = await requestJson('/conta/ong/dados');
        if (data) {
            const editableFields = [
                'descricao_ong', 'email_ong', 'tel_ong', 'cep_ong', 'logradouro_ong',
                'bairro_ong', 'cidade_ong', 'uf_ong', 'site_url_ong', 'login_ong'
            ];
            for (const field of editableFields) {
                const input = form.elements[field];
                if (input && data[field] !== undefined) {
                    input.value = data[field] || '';
                }
            }
            showMessage(feedback, '', 'info');
            return data;
        }
    } catch (error) {
        showMessage(feedback, 'Não foi possível carregar os dados. Recarregue a página.', 'error');
    }
    return null;
}

function showFieldError(form, fieldName, message) {
    const field = form.querySelector(`[data-field-error="${fieldName}"]`);
    if (field) {
        field.textContent = message;
        field.hidden = !message;
    }
    const input = form.elements[fieldName];
    if (input) {
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll('[data-field-error]').forEach(el => {
        el.textContent = '';
        el.hidden = true;
    });
    form.querySelectorAll('[aria-invalid]').forEach(el => {
        el.removeAttribute('aria-invalid');
    });
}

function handleValidationErrors(form, errors) {
    if (!errors) return;
    let firstInvalid = null;
    for (const [field, messages] of Object.entries(errors)) {
        showFieldError(form, field, messages[0] || '');
        if (!firstInvalid) {
            firstInvalid = form.elements[field];
        }
    }
    firstInvalid?.focus();
}

/**
 * Inicializa edição de perfil Pessoa
 */
function initializePersonProfileEdit(root) {
    const form = root.querySelector('[data-person-edit-form]');
    if (!form) return;

    const submitButton = form.querySelector('[type="submit"]');
    const feedback = root.querySelector('[data-person-edit-feedback]');
    let personId = root.dataset.personId;
    const csrfToken = root.dataset.csrfToken;

    // Carrega dados ao inicializar
    loadPersonData(form, feedback).then(data => {
        if (data && data.pessoa_id) {
            personId = data.pessoa_id;
            root.dataset.personId = personId;
        }
    });

    if (!personId && !root.dataset.personId) {
        console.warn('account-profile: data-person-id será carregado via API');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitButton.disabled) return;

        submitButton.disabled = true;
        clearFieldErrors(form);
        showMessage(feedback, 'Salvando alterações…', 'info');

        try {
            const formData = new FormData(form);
            const body = {};
            
            // Coleta apenas campos permitidos para edição
            const allowedFields = [
                'email_pessoa', 'tele_pessoa', 'cep_pessoa', 'logradouro_pessoa',
                'compl_pessoa', 'cidade_pessoa', 'bairro_pessoa', 'uf_pessoa'
            ];
            
            for (const field of allowedFields) {
                if (formData.has(field)) {
                    const value = formData.get(field);
                    if (value !== null && value !== '') {
                        body[field] = value;
                    }
                }
            }

            const { data, message } = await requestJson(`/conta/pessoa/${personId}/dados`, {
                method: 'PATCH',
                body,
                csrfToken,
            });

            showMessage(feedback, message || 'Dados atualizados com sucesso.', 'success');
            
            // Atualiza campos no formulário com dados retornados
            if (data) {
                for (const [key, value] of Object.entries(data)) {
                    const input = form.elements[key];
                    if (input && value !== undefined) {
                        input.value = value;
                    }
                }
            }
        } catch (error) {
            if (error.status === 422) {
                handleValidationErrors(form, error.errors);
                showMessage(feedback, 'Revise os campos indicados.', 'error');
            } else if (error.status === 403) {
                showMessage(feedback, 'Você não tem permissão para editar este perfil.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível salvar as alterações.', 'error');
            }
        } finally {
            submitButton.disabled = false;
        }
    });
}

/**
 * Inicializa upload de foto de perfil Pessoa
 */
function initializePersonPhotoUpload(root) {
    const container = root.querySelector('[data-person-photo]');
    if (!container) return;

    const input = container.querySelector('[data-photo-input]');
    const preview = container.querySelector('[data-photo-preview]');
    const uploadButton = container.querySelector('[data-photo-upload]');
    const removeButton = container.querySelector('[data-photo-remove]');
    const feedback = container.querySelector('[data-photo-feedback]');
    const csrfToken = root.dataset.csrfToken;

    if (!input) return;

    let selectedFile = null;

    function currentPersonId() {
        return root.dataset.personId || '';
    }

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            showMessage(feedback, 'Selecione uma imagem JPEG, PNG ou WebP.', 'error');
            input.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showMessage(feedback, 'A imagem deve ter no máximo 5 MiB.', 'error');
            input.value = '';
            return;
        }

        selectedFile = file;
        if (preview) {
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        }
        const placeholder = root.querySelector('[data-photo-placeholder]');
        if (placeholder) placeholder.hidden = true;
        showMessage(feedback, `Selecionado: ${file.name}. Clique em Enviar.`, 'info');
        
        if (uploadButton) uploadButton.hidden = false;
    });

    uploadButton?.addEventListener('click', async () => {
        const personId = currentPersonId();
        if (!selectedFile || uploadButton.disabled) return;
        if (!personId) {
            showMessage(feedback, 'A conta ainda está carregando. Aguarde e tente de novo.', 'error');
            return;
        }

        uploadButton.disabled = true;
        showMessage(feedback, 'Enviando imagem…', 'info');

        try {
            const formData = new FormData();
            formData.append('imagem', selectedFile);

            const { data, message } = await requestJson(`/conta/pessoa/${personId}/imagem`, {
                method: 'POST',
                body: formData,
                csrfToken,
            });

            showMessage(feedback, message || 'Imagem atualizada com sucesso.', 'success');
            applyStoredPhoto(root, data?.url);
            
            selectedFile = null;
            input.value = '';
            uploadButton.hidden = true;
        } catch (error) {
            if (error.status === 422) {
                showMessage(feedback, error.errors?.imagem?.[0] || 'Imagem inválida.', 'error');
            } else if (error.status === 503) {
                showMessage(feedback, 'Serviço de armazenamento indisponível. Tente novamente.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível enviar a imagem.', 'error');
            }
        } finally {
            uploadButton.disabled = false;
        }
    });

    removeButton?.addEventListener('click', async () => {
        if (removeButton.disabled) return;

        removeButton.disabled = true;
        showMessage(feedback, 'Removendo imagem…', 'info');

        try {
            const personId = currentPersonId();
            if (!personId) {
                showMessage(feedback, 'A conta ainda está carregando. Aguarde e tente de novo.', 'error');
                return;
            }
            await requestJson(`/conta/pessoa/${personId}/imagem`, {
                method: 'DELETE',
                csrfToken,
            });

            showMessage(feedback, 'Imagem removida com sucesso.', 'success');
            
            if (preview) {
                preview.src = '';
                preview.hidden = true;
            }
            const placeholder = root.querySelector('[data-photo-placeholder]');
            if (placeholder) placeholder.hidden = false;
            removeButton.hidden = true;
        } catch (error) {
            showMessage(feedback, error.message || 'Não foi possível remover a imagem.', 'error');
        } finally {
            removeButton.disabled = false;
        }
    });
}

/**
 * Inicializa edição de perfil ONG
 */
function initializeOngProfileEdit(root) {
    const form = root.querySelector('[data-ong-edit-form]');
    if (!form) return;

    const submitButton = form.querySelector('[type="submit"]');
    const feedback = root.querySelector('[data-ong-edit-feedback]');
    let ongId = root.dataset.ongId;
    const csrfToken = root.dataset.csrfToken;

    // Carrega dados ao inicializar
    loadOngData(form, feedback).then(data => {
        if (data && data.ong_id) {
            ongId = data.ong_id;
            root.dataset.ongId = ongId;
        }
    });

    if (!ongId && !root.dataset.ongId) {
        console.warn('account-profile: data-ong-id será carregado via API');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitButton.disabled) return;

        submitButton.disabled = true;
        clearFieldErrors(form);
        showMessage(feedback, 'Salvando alterações…', 'info');

        try {
            const formData = new FormData(form);
            const body = {};
            
            // Campos permitidos para edição de ONG
            const allowedFields = [
                'descricao_ong', 'email_ong', 'tel_ong', 'cep_ong', 'logradouro_ong',
                'bairro_ong', 'cidade_ong', 'uf_ong', 'site_url_ong', 'login_ong'
            ];
            
            for (const field of allowedFields) {
                if (formData.has(field)) {
                    const value = formData.get(field);
                    if (value !== null && value !== '') {
                        body[field] = value;
                    }
                }
            }

            const { data, message } = await requestJson(`/conta/ong/${ongId}/dados`, {
                method: 'PATCH',
                body,
                csrfToken,
            });

            showMessage(feedback, message || 'Dados atualizados com sucesso.', 'success');
            
            if (data) {
                for (const [key, value] of Object.entries(data)) {
                    const input = form.elements[key];
                    if (input && value !== undefined) {
                        input.value = value;
                    }
                }
            }
        } catch (error) {
            if (error.status === 422) {
                handleValidationErrors(form, error.errors);
                showMessage(feedback, 'Revise os campos indicados.', 'error');
            } else if (error.status === 403) {
                showMessage(feedback, 'Você não tem permissão para editar esta ONG.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível salvar as alterações.', 'error');
            }
        } finally {
            submitButton.disabled = false;
        }
    });
}

/**
 * Inicializa upload de foto de perfil ONG
 */
function initializeOngPhotoUpload(root) {
    const container = root.querySelector('[data-ong-photo]');
    if (!container) return;

    const input = container.querySelector('[data-photo-input]');
    const preview = container.querySelector('[data-photo-preview]');
    const uploadButton = container.querySelector('[data-photo-upload]');
    const removeButton = container.querySelector('[data-photo-remove]');
    const feedback = container.querySelector('[data-photo-feedback]');
    const ongId = root.dataset.ongId;
    const csrfToken = root.dataset.csrfToken;

    if (!input || !ongId) return;

    let selectedFile = null;

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            showMessage(feedback, 'Selecione uma imagem JPEG, PNG ou WebP.', 'error');
            input.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showMessage(feedback, 'A imagem deve ter no máximo 5 MiB.', 'error');
            input.value = '';
            return;
        }

        selectedFile = file;
        showMessage(feedback, `Selecionado: ${file.name} (${(file.size / 1024).toFixed(1)} KiB)`, 'info');
        
        if (uploadButton) uploadButton.hidden = false;
    });

    uploadButton?.addEventListener('click', async () => {
        if (!selectedFile || uploadButton.disabled) return;

        uploadButton.disabled = true;
        showMessage(feedback, 'Enviando imagem…', 'info');

        try {
            const formData = new FormData();
            formData.append('imagem', selectedFile);

            const { data, message } = await requestJson(`/conta/ong/${ongId}/imagem`, {
                method: 'POST',
                body: formData,
                csrfToken,
            });

            showMessage(feedback, message || 'Imagem atualizada com sucesso.', 'success');
            
            if (data?.url && preview) {
                preview.src = data.url;
                preview.hidden = false;
            }
            
            if (removeButton) removeButton.hidden = false;
            selectedFile = null;
            input.value = '';
            uploadButton.hidden = true;
        } catch (error) {
            if (error.status === 422) {
                showMessage(feedback, error.errors?.imagem?.[0] || 'Imagem inválida.', 'error');
            } else if (error.status === 503) {
                showMessage(feedback, 'Serviço de armazenamento indisponível. Tente novamente.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível enviar a imagem.', 'error');
            }
        } finally {
            uploadButton.disabled = false;
        }
    });

    removeButton?.addEventListener('click', async () => {
        if (removeButton.disabled) return;

        removeButton.disabled = true;
        showMessage(feedback, 'Removendo imagem…', 'info');

        try {
            await requestJson(`/conta/ong/${ongId}/imagem`, {
                method: 'DELETE',
                csrfToken,
            });

            showMessage(feedback, 'Imagem removida com sucesso.', 'success');
            
            if (preview) {
                preview.src = '';
                preview.hidden = true;
            }
            removeButton.hidden = true;
        } catch (error) {
            showMessage(feedback, error.message || 'Não foi possível remover a imagem.', 'error');
        } finally {
            removeButton.disabled = false;
        }
    });
}

/**
 * Função principal de inicialização
 */
export function initializeAccountProfile() {
    // Edição de perfil Pessoa
    const personEdit = document.querySelector('[data-person-profile-edit]');
    if (personEdit) {
        initializePersonProfileEdit(personEdit);
        initializePersonPhotoUpload(personEdit);
    }

    // Edição de perfil ONG
    const ongEdit = document.querySelector('[data-ong-profile-edit]');
    if (ongEdit) {
        initializeOngProfileEdit(ongEdit);
        initializeOngPhotoUpload(ongEdit);
    }

    initializeOwnMetrics();
}

async function initializeOwnMetrics() {
    const reliability = document.querySelector('[data-reliability]');
    if (reliability) {
        const status = reliability.querySelector('[data-reliability-status]');
        try {
            const { data } = await requestJson(reliability.dataset.reliabilityUrl);
            status.textContent = data?.formada
                ? `Confiabilidade ${data.score} de 100, com ${data.participacoes_confirmadas} participações confirmadas.`
                : (data?.estado || 'A confiabilidade ainda está em formação.');
        } catch (error) {
            status.textContent = error.message || 'Não foi possível consultar a confiabilidade.';
        }
    }

    const xp = document.querySelector('[data-own-xp-status]');
    if (!xp) return;
    try {
        const { data } = await requestJson('/conta/pessoa/dados');
        const total = Number(data?.xp_pessoa ?? 0);
        xp.textContent = `Total de XP: ${total}. O extrato detalhado não faz parte desta versão.`;
    } catch (error) {
        xp.textContent = error.message || 'Não foi possível consultar o XP.';
    }
}
