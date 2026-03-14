// Code JS global pour l'application Formtec
// Ajoute des comportements communs (confirmations, auto-dismiss des alertes, etc.)

document.addEventListener('DOMContentLoaded', () => {
    // Auto-fermeture des alertes après 5s
    document.querySelectorAll('.alert-auto-close').forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            alert.addEventListener('transitionend', () => alert.remove());
        }, 5000);
    });

    // Confirmation sur les actions de suppression
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (event) => {
            const message = el.getAttribute('data-confirm') || 'Êtes-vous sûr(e) ?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Mise au focus du premier champ de formulaire (optionnel)
    const firstInput = document.querySelector('form input, form select, form textarea');
    if (firstInput) {
        firstInput.focus();
    }
});
// app.js - Gestion complète Institut Formtec (frontend JS moderne)

document.addEventListener('DOMContentLoaded', () => {
    // ────────────────────────────────────────────────
    // Configuration
    // ────────────────────────────────────────────────
    const API_BASE = '/Gestion/api/';  // adapte selon ton dossier

    const entities = {
        etudiants:    { endpoint: 'etudiants.php',   title: 'Étudiants'   },
        formateurs:   { endpoint: 'formateurs.php',  title: 'Formateurs'  },
        formations:   { endpoint: 'formations.php',  title: 'Formations'  },
        inscriptions: { endpoint: 'inscriptions.php', title: 'Inscriptions'}
    };

    let currentEntity = 'etudiants';
    let currentData = [];

    // ────────────────────────────────────────────────
    // Éléments DOM principaux
    // ────────────────────────────────────────────────
    const tableBody       = document.getElementById('table-body');
    const entityTitle     = document.getElementById('entity-title');
    const modalTitle      = document.getElementById('modal-title');
    const form            = document.getElementById('entity-form');
    const saveBtn         = document.getElementById('save-btn');
    const entityTabs      = document.querySelectorAll('.nav-link[data-entity]');

    const modal = new bootstrap.Modal(document.getElementById('entityModal'));

    // ────────────────────────────────────────────────
    // Fonctions utilitaires
    // ────────────────────────────────────────────────
    async function fetchData(entity) {
        try {
            const res = await fetch(API_BASE + entities[entity].endpoint);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return await res.json();
        } catch (err) {
            console.error(err);
            alert("Erreur lors du chargement des données");
            return [];
        }
    }

    async function saveData(entity, data, id = null) {
        const method = id ? 'PUT' : 'POST';
        const url = API_BASE + entities[entity].endpoint + (id ? `/${id}` : '');

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!res.ok) throw new Error(await res.text());
            return await res.json();
        } catch (err) {
            console.error(err);
            alert("Erreur lors de la sauvegarde");
            throw err;
        }
    }

    async function deleteData(entity, id) {
        if (!confirm("Confirmer la suppression ?")) return;
        try {
            const res = await fetch(API_BASE + entities[entity].endpoint + `/${id}`, {
                method: 'DELETE'
            });
            if (!res.ok) throw new Error("Échec suppression");
            alert("Supprimé avec succès");
        } catch (err) {
            alert("Erreur suppression");
        }
    }

    function renderTable(data, entity) {
        tableBody.innerHTML = '';
        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Aucune donnée</td></tr>';
            return;
        }

        data.forEach(item => {
            const tr = document.createElement('tr');
            let html = '';

            if (entity === 'etudiants') {
                html = `
                    <td>${item.id}</td>
                    <td>${item.nom} ${item.prenom}</td>
                    <td>${item.email || '-'}</td>
                    <td>${item.telephone || '-'}</td>
                    <td>${item.date_inscription || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}">Modifier</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Supprimer</button>
                    </td>
                `;
            } else if (entity === 'formateurs') {
                html = `
                    <td>${item.id}</td>
                    <td>${item.nom} ${item.prenom}</td>
                    <td>${item.specialite || '-'}</td>
                    <td>${item.email || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}">Modifier</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Supprimer</button>
                    </td>
                `;
            } else if (entity === 'formations') {
                html = `
                    <td>${item.id}</td>
                    <td>${item.nom}</td>
                    <td>${item.prix} FCFA</td>
                    <td>${item.duree_mois || '-'} mois</td>
                    <td>${item.formateur_nom || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}">Modifier</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Supprimer</button>
                    </td>
                `;
            } else if (entity === 'inscriptions') {
                html = `
                    <td>${item.id}</td>
                    <td>${item.etudiant_nom}</td>
                    <td>${item.formation_nom}</td>
                    <td>${item.date_inscription}</td>
                    <td><span class="badge bg-${item.statut === 'en cours' ? 'primary' : item.statut === 'terminée' ? 'success' : 'danger'}">${item.statut}</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${item.id}">Modifier</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Supprimer</button>
                    </td>
                `;
            }

            tr.innerHTML = html;
            tableBody.appendChild(tr);
        });
    }

    // ────────────────────────────────────────────────
    // Chargement initial + navigation entre onglets
    // ────────────────────────────────────────────────
    async function loadEntity(entity) {
        currentEntity = entity;
        entityTitle.textContent = entities[entity].title;
        currentData = await fetchData(entity);
        renderTable(currentData, entity);
    }

    entityTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const entity = e.target.dataset.entity;
            loadEntity(entity);
        });
    });

    // ────────────────────────────────────────────────
    // Ajout / Modification via modal
    // ────────────────────────────────────────────────
    document.getElementById('add-btn').addEventListener('click', () => {
        form.reset();
        modalTitle.textContent = `Ajouter ${entities[currentEntity].title.slice(0, -1)}`;
        saveBtn.dataset.mode = 'add';
        saveBtn.dataset.id = '';
        modal.show();
    });

    tableBody.addEventListener('click', async (e) => {
        if (e.target.classList.contains('edit-btn')) {
            const id = e.target.dataset.id;
            const item = currentData.find(i => i.id == id);
            if (!item) return;

            modalTitle.textContent = `Modifier ${entities[currentEntity].title.slice(0, -1)}`;
            saveBtn.dataset.mode = 'edit';
            saveBtn.dataset.id = id;

            // Remplir le formulaire selon l'entité (à compléter selon tes champs)
            Object.keys(item).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) input.value = item[key];
            });

            modal.show();
        }

        if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            await deleteData(currentEntity, id);
            await loadEntity(currentEntity);
        }
    });

    saveBtn.addEventListener('click', async () => {
        const mode = saveBtn.dataset.mode;
        const id = saveBtn.dataset.id;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            await saveData(currentEntity, data, mode === 'edit' ? id : null);
            modal.hide();
            await loadEntity(currentEntity);
        } catch {}
    });

    // Chargement de la première entité au démarrage
    loadEntity('etudiants');
});