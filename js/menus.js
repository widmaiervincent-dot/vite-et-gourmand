// Filtres des menus sans recharger la page
const formFiltres = document.getElementById('formFiltres');
const listeMenus = document.getElementById('listeMenus');
const nbResultats = document.getElementById('nbResultats');

// pour éviter les failles XSS quand on insère du texte dans le HTML
function echapper(texte) {
    const div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

function chargerMenus() {
    const params = new URLSearchParams(new FormData(formFiltres));

    fetch('/api/filtre_menus.php?' + params.toString())
        .then(function (reponse) {
            return reponse.json();
        })
        .then(function (menus) {
            afficherMenus(menus);
        })
        .catch(function (erreur) {
            console.log(erreur);
            listeMenus.innerHTML = '<p>Erreur lors du chargement des menus.</p>';
        });
}

function afficherMenus(menus) {
    listeMenus.innerHTML = '';
    nbResultats.textContent = menus.length + ' menu(s) trouvé(s)';

    if (menus.length === 0) {
        listeMenus.innerHTML = '<p>Aucun menu ne correspond à votre recherche.</p>';
        return;
    }

    menus.forEach(function (menu) {
        let html = '<div class="col-md-6 col-lg-4 mb-4">';
        html += '<article class="card carte-menu">';
        if (menu.image) {
            html += '<img src="/' + echapper(menu.image) + '" class="card-img-top" alt="' + echapper(menu.image_alt || '') + '">';
        }
        html += '<div class="card-body d-flex flex-column">';
        html += '<h2 class="h5 card-title">' + echapper(menu.titre) + '</h2>';
        html += '<p class="small"><span class="badge bg-secondary">' + echapper(menu.theme) + '</span> ';
        html += '<span class="badge bg-success">' + echapper(menu.regime) + '</span></p>';
        html += '<p class="card-text">' + echapper(menu.description) + '</p>';
        html += '<p class="mb-1">Minimum ' + echapper(menu.nb_personnes_min) + ' personnes</p>';
        html += '<p class="fw-bold">' + Number(menu.prix).toFixed(2).replace('.', ',') + ' €</p>';
        if (menu.stock <= 0) {
            html += '<p class="text-danger">Plus disponible pour le moment</p>';
        }
        html += '<a href="/menu_detail.php?id=' + menu.id + '" class="btn btn-bordeaux mt-auto">Voir le détail<span class="visually-hidden"> du ' + echapper(menu.titre) + '</span></a>';
        html += '</div></article></div>';

        listeMenus.innerHTML += html;
    });
}

// on recharge à chaque changement dans le formulaire
formFiltres.addEventListener('input', chargerMenus);
formFiltres.addEventListener('reset', function () {
    // le reset se fait après l'évènement donc petit délai
    setTimeout(chargerMenus, 10);
});
formFiltres.addEventListener('submit', function (e) {
    e.preventDefault();
    chargerMenus();
});

// premier chargement
chargerMenus();
