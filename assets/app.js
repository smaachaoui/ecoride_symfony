// assets/app.js

import './styles/app.css';

/*
 * ============================================
 * ECORIDE - Application JavaScript
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // Gestion des onglets avec hash URL
    // ============================================
    initTabsWithHash();

    // ============================================
    // Gestion des étoiles de notation
    // ============================================
    initRatingStars();

    // ============================================
    // Récapitulatif prix covoiturage
    // ============================================
    initPrixRecap();

});

/**
 * Gestion des onglets Bootstrap avec persistance via hash URL
 */
function initTabsWithHash() {
    // Récupérer le hash de l'URL et activer l'onglet correspondant
    let hash = window.location.hash;
    if (hash) {
        const tabTrigger = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    // Mettre à jour l'URL quand on change d'onglet
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tabEl) {
        tabEl.addEventListener('shown.bs.tab', function(event) {
            history.pushState(null, null, event.target.getAttribute('data-bs-target'));
        });
    });
}

/**
 * Gestion des étoiles de notation interactives
 */
function initRatingStars() {
    document.querySelectorAll('.rating-input').forEach(function(ratingContainer) {
        const labels = ratingContainer.querySelectorAll('.rating-label');
        const inputs = ratingContainer.querySelectorAll('.rating-star');

        labels.forEach(function(label, index) {
            // Hover : afficher les étoiles pleines
            label.addEventListener('mouseenter', function() {
                labels.forEach(function(l, i) {
                    const icon = l.querySelector('i');
                    if (i <= index) {
                        icon.classList.remove('bi-star');
                        icon.classList.add('bi-star-fill');
                    } else {
                        icon.classList.remove('bi-star-fill');
                        icon.classList.add('bi-star');
                    }
                });
            });

            // Click : marquer comme actif
            label.addEventListener('click', function() {
                labels.forEach(function(l, i) {
                    l.classList.toggle('active', i <= index);
                });
            });
        });

        // Mouse leave : revenir à l'état sélectionné
        ratingContainer.addEventListener('mouseleave', function() {
            const checkedInput = ratingContainer.querySelector('.rating-star:checked');
            const checkedIndex = checkedInput ? Array.from(inputs).indexOf(checkedInput) : -1;

            labels.forEach(function(l, i) {
                const icon = l.querySelector('i');
                if (i <= checkedIndex) {
                    icon.classList.remove('bi-star');
                    icon.classList.add('bi-star-fill');
                } else {
                    icon.classList.remove('bi-star-fill');
                    icon.classList.add('bi-star');
                }
            });
        });
    });
}

/**
 * Récapitulatif dynamique du prix pour la création de covoiturage
 */
function initPrixRecap() {
    const prixInput = document.getElementById('creer_covoiturage_prix');
    const prixAffiche = document.getElementById('prixAffiche');
    const prixChauffeur = document.getElementById('prixChauffeur');

    if (prixInput && prixAffiche && prixChauffeur) {
        function updateRecap() {
            const prix = parseInt(prixInput.value) || 0;
            const gain = Math.max(0, prix - 2);
            prixAffiche.textContent = prix;
            prixChauffeur.textContent = gain;
        }

        prixInput.addEventListener('input', updateRecap);
        updateRecap(); // Initialisation
    }
}