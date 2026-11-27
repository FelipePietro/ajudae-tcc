import { requestJson } from '../core/http.js';

/**
 * Módulo de integração para catálogos e preferências.
 * Bloco 9 - João Pedro - Tarefa 4
 * 
 * Endpoints consumidos:
 * - GET /catalogos/habilidades
 * - GET /catalogos/recursos
 * - GET /catalogos/causas
 * - GET /catalogos/categorias-evento
 * - GET /conta/pessoa/preferencias
 * - POST /conta/pessoa/{id}/habilidades
 * - DELETE /conta/pessoa/{id}/habilidades/{habilidade}
 * - POST /conta/pessoa/{id}/recursos
 * - DELETE /conta/pessoa/{id}/recursos/{recurso}
 * - POST /conta/pessoa/{id}/causas
 * - DELETE /conta/pessoa/{id}/causas/{causa}
 */

function showMessage(element, text, type = 'info') {
    if (!element) return;
    element.textContent = text;
    element.hidden = !text;
    element.className = `account-feedback account-feedback--${type}`;
}

/**
 * Inicializa gerenciamento de habilidades
 */
function initializeSkills(root) {
    const container = root.querySelector('[data-skills-manager]');
    if (!container) return;

    const list = container.querySelector('[data-skills-list]');
    const catalogSelect = container.querySelector('[data-skills-catalog]');
    const levelSelect = container.querySelector('[data-skills-level]');
    const addButton = container.querySelector('[data-skills-add]');
    const feedback = container.querySelector('[data-skills-feedback]');
    const personId = root.dataset.personId;
    const csrfToken = root.dataset.csrfToken;

    if (!list || !personId) return;

    let catalog = [];
    let currentSkills = [];

    async function loadCatalog() {
        try {
            const { data } = await requestJson('/catalogos/habilidades');
            catalog = data?.data || data || [];
            
            if (catalogSelect) {
                catalogSelect.innerHTML = '<option value="">Selecione uma habilidade</option>';
                for (const skill of catalog) {
                    const option = document.createElement('option');
                    option.value = skill.habilidade_id;
                    option.textContent = skill.nome_habilidade;
                    catalogSelect.appendChild(option);
                }
            }
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar o catálogo de habilidades.', 'error');
        }
    }

    async function loadCurrentSkills() {
        try {
            const { data } = await requestJson('/conta/pessoa/preferencias');
            currentSkills = data?.habilidades || [];
            renderSkills();
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar suas habilidades.', 'error');
        }
    }

    function renderSkills() {
        list.innerHTML = '';
        
        if (currentSkills.length === 0) {
            list.innerHTML = '<p class="account-empty">Nenhuma habilidade cadastrada.</p>';
            return;
        }

        for (const skill of currentSkills) {
            const item = document.createElement('div');
            item.className = 'preference-item';
            item.innerHTML = `
                <span class="preference-name">${skill.nome_habilidade}</span>
                <span class="preference-level">Nível ${skill.pivot?.nivel_habilidade || skill.nivel_habilidade || '—'}</span>
                <button type="button" class="preference-remove" data-remove-skill="${skill.habilidade_id}" aria-label="Remover ${skill.nome_habilidade}">×</button>
            `;
            list.appendChild(item);
        }

        // Adiciona eventos de remoção
        list.querySelectorAll('[data-remove-skill]').forEach(button => {
            button.addEventListener('click', () => removeSkill(button.dataset.removeSkill));
        });
    }

    async function addSkill() {
        const skillId = catalogSelect?.value;
        const level = levelSelect?.value || 3;

        if (!skillId) {
            showMessage(feedback, 'Selecione uma habilidade.', 'error');
            return;
        }

        // Verifica se já existe
        if (currentSkills.some(s => String(s.habilidade_id) === String(skillId))) {
            showMessage(feedback, 'Esta habilidade já está cadastrada.', 'error');
            return;
        }

        if (addButton) addButton.disabled = true;
        showMessage(feedback, 'Adicionando habilidade…', 'info');

        try {
            await requestJson(`/conta/pessoa/${personId}/habilidades`, {
                method: 'POST',
                body: {
                    habilidade_id: parseInt(skillId, 10),
                    nivel_habilidade: parseInt(level, 10),
                },
                csrfToken,
            });

            showMessage(feedback, 'Habilidade adicionada com sucesso.', 'success');
            if (catalogSelect) catalogSelect.value = '';
            await loadCurrentSkills();
        } catch (error) {
            if (error.status === 409) {
                showMessage(feedback, 'Esta habilidade já está cadastrada.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível adicionar a habilidade.', 'error');
            }
        } finally {
            if (addButton) addButton.disabled = false;
        }
    }

    async function removeSkill(skillId) {
        const button = list.querySelector(`[data-remove-skill="${skillId}"]`);
        if (button) button.disabled = true;

        try {
            await requestJson(`/conta/pessoa/${personId}/habilidades/${skillId}`, {
                method: 'DELETE',
                csrfToken,
            });

            showMessage(feedback, 'Habilidade removida.', 'success');
            await loadCurrentSkills();
        } catch (error) {
            showMessage(feedback, error.message || 'Não foi possível remover a habilidade.', 'error');
            if (button) button.disabled = false;
        }
    }

    addButton?.addEventListener('click', addSkill);
    
    // Carrega dados iniciais
    loadCatalog();
    loadCurrentSkills();
}

/**
 * Inicializa gerenciamento de recursos
 */
function initializeResources(root) {
    const container = root.querySelector('[data-resources-manager]');
    if (!container) return;

    const list = container.querySelector('[data-resources-list]');
    const catalogSelect = container.querySelector('[data-resources-catalog]');
    const detailsInput = container.querySelector('[data-resources-details]');
    const addButton = container.querySelector('[data-resources-add]');
    const feedback = container.querySelector('[data-resources-feedback]');
    const personId = root.dataset.personId;
    const csrfToken = root.dataset.csrfToken;

    if (!list || !personId) return;

    let catalog = [];
    let currentResources = [];

    async function loadCatalog() {
        try {
            const { data } = await requestJson('/catalogos/recursos');
            catalog = data?.data || data || [];
            
            if (catalogSelect) {
                catalogSelect.innerHTML = '<option value="">Selecione um recurso</option>';
                for (const resource of catalog) {
                    const option = document.createElement('option');
                    option.value = resource.recurso_id;
                    option.textContent = resource.nome_recurso;
                    catalogSelect.appendChild(option);
                }
            }
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar o catálogo de recursos.', 'error');
        }
    }

    async function loadCurrentResources() {
        try {
            const { data } = await requestJson('/conta/pessoa/preferencias');
            currentResources = data?.recursos || [];
            renderResources();
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar seus recursos.', 'error');
        }
    }

    function renderResources() {
        list.innerHTML = '';
        
        if (currentResources.length === 0) {
            list.innerHTML = '<p class="account-empty">Nenhum recurso cadastrado.</p>';
            return;
        }

        for (const resource of currentResources) {
            const item = document.createElement('div');
            item.className = 'preference-item';
            const details = resource.pivot?.detalhes_recurso || resource.detalhes_recurso || '';
            item.innerHTML = `
                <span class="preference-name">${resource.nome_recurso}</span>
                ${details ? `<span class="preference-details">${details}</span>` : ''}
                <button type="button" class="preference-remove" data-remove-resource="${resource.recurso_id}" aria-label="Remover ${resource.nome_recurso}">×</button>
            `;
            list.appendChild(item);
        }

        list.querySelectorAll('[data-remove-resource]').forEach(button => {
            button.addEventListener('click', () => removeResource(button.dataset.removeResource));
        });
    }

    async function addResource() {
        const resourceId = catalogSelect?.value;
        const details = detailsInput?.value?.trim() || '';

        if (!resourceId) {
            showMessage(feedback, 'Selecione um recurso.', 'error');
            return;
        }

        if (currentResources.some(r => String(r.recurso_id) === String(resourceId))) {
            showMessage(feedback, 'Este recurso já está cadastrado.', 'error');
            return;
        }

        if (addButton) addButton.disabled = true;
        showMessage(feedback, 'Adicionando recurso…', 'info');

        try {
            const body = { recurso_id: parseInt(resourceId, 10) };
            if (details) body.detalhes_recurso = details.substring(0, 255);

            await requestJson(`/conta/pessoa/${personId}/recursos`, {
                method: 'POST',
                body,
                csrfToken,
            });

            showMessage(feedback, 'Recurso adicionado com sucesso.', 'success');
            if (catalogSelect) catalogSelect.value = '';
            if (detailsInput) detailsInput.value = '';
            await loadCurrentResources();
        } catch (error) {
            if (error.status === 409) {
                showMessage(feedback, 'Este recurso já está cadastrado.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível adicionar o recurso.', 'error');
            }
        } finally {
            if (addButton) addButton.disabled = false;
        }
    }

    async function removeResource(resourceId) {
        const button = list.querySelector(`[data-remove-resource="${resourceId}"]`);
        if (button) button.disabled = true;

        try {
            await requestJson(`/conta/pessoa/${personId}/recursos/${resourceId}`, {
                method: 'DELETE',
                csrfToken,
            });

            showMessage(feedback, 'Recurso removido.', 'success');
            await loadCurrentResources();
        } catch (error) {
            showMessage(feedback, error.message || 'Não foi possível remover o recurso.', 'error');
            if (button) button.disabled = false;
        }
    }

    addButton?.addEventListener('click', addResource);
    loadCatalog();
    loadCurrentResources();
}

/**
 * Inicializa gerenciamento de causas
 */
function initializeCauses(root) {
    const container = root.querySelector('[data-causes-manager]');
    if (!container) return;

    const list = container.querySelector('[data-causes-list]');
    const catalogSelect = container.querySelector('[data-causes-catalog]');
    const addButton = container.querySelector('[data-causes-add]');
    const feedback = container.querySelector('[data-causes-feedback]');
    const personId = root.dataset.personId;
    const csrfToken = root.dataset.csrfToken;

    if (!list || !personId) return;

    let catalog = [];
    let currentCauses = [];

    async function loadCatalog() {
        try {
            const { data } = await requestJson('/catalogos/causas');
            catalog = data?.data || data || [];
            
            if (catalogSelect) {
                catalogSelect.innerHTML = '<option value="">Selecione uma causa</option>';
                for (const cause of catalog) {
                    const option = document.createElement('option');
                    option.value = cause.causa_id;
                    option.textContent = cause.nome_causa;
                    catalogSelect.appendChild(option);
                }
            }
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar o catálogo de causas.', 'error');
        }
    }

    async function loadCurrentCauses() {
        try {
            const { data } = await requestJson('/conta/pessoa/preferencias');
            currentCauses = data?.causas || [];
            renderCauses();
        } catch (error) {
            showMessage(feedback, 'Não foi possível carregar suas causas.', 'error');
        }
    }

    function renderCauses() {
        list.innerHTML = '';
        
        if (currentCauses.length === 0) {
            list.innerHTML = '<p class="account-empty">Nenhuma causa cadastrada.</p>';
            return;
        }

        for (const cause of currentCauses) {
            const item = document.createElement('div');
            item.className = 'preference-item';
            item.innerHTML = `
                <span class="preference-name">${cause.nome_causa}</span>
                <button type="button" class="preference-remove" data-remove-cause="${cause.causa_id}" aria-label="Remover ${cause.nome_causa}">×</button>
            `;
            list.appendChild(item);
        }

        list.querySelectorAll('[data-remove-cause]').forEach(button => {
            button.addEventListener('click', () => removeCause(button.dataset.removeCause));
        });
    }

    async function addCause() {
        const causeId = catalogSelect?.value;

        if (!causeId) {
            showMessage(feedback, 'Selecione uma causa.', 'error');
            return;
        }

        if (currentCauses.some(c => String(c.causa_id) === String(causeId))) {
            showMessage(feedback, 'Esta causa já está cadastrada.', 'error');
            return;
        }

        if (addButton) addButton.disabled = true;
        showMessage(feedback, 'Adicionando causa…', 'info');

        try {
            await requestJson(`/conta/pessoa/${personId}/causas`, {
                method: 'POST',
                body: { causa_id: parseInt(causeId, 10) },
                csrfToken,
            });

            showMessage(feedback, 'Causa adicionada com sucesso.', 'success');
            if (catalogSelect) catalogSelect.value = '';
            await loadCurrentCauses();
        } catch (error) {
            if (error.status === 409) {
                showMessage(feedback, 'Esta causa já está cadastrada.', 'error');
            } else {
                showMessage(feedback, error.message || 'Não foi possível adicionar a causa.', 'error');
            }
        } finally {
            if (addButton) addButton.disabled = false;
        }
    }

    async function removeCause(causeId) {
        const button = list.querySelector(`[data-remove-cause="${causeId}"]`);
        if (button) button.disabled = true;

        try {
            await requestJson(`/conta/pessoa/${personId}/causas/${causeId}`, {
                method: 'DELETE',
                csrfToken,
            });

            showMessage(feedback, 'Causa removida.', 'success');
            await loadCurrentCauses();
        } catch (error) {
            showMessage(feedback, error.message || 'Não foi possível remover a causa.', 'error');
            if (button) button.disabled = false;
        }
    }

    addButton?.addEventListener('click', addCause);
    loadCatalog();
    loadCurrentCauses();
}

/**
 * Função principal de inicialização
 */
export function initializeAccountPreferences() {
    const root = document.querySelector('[data-preferences-manager]');
    if (!root) return;

    initializeSkills(root);
    initializeResources(root);
    initializeCauses(root);
}
