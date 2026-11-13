// calcul du prix en direct sur la page de commande
// (le vrai calcul est refait en PHP à la validation, ici c'est juste pour l'affichage)

const selectMenu = document.getElementById('menu_id');
const champNb = document.getElementById('nb_personnes');
const champVille = document.getElementById('ville');
const champDistance = document.getElementById('distance_km');
const blocDistance = document.getElementById('blocDistance');

function formatEuro(nombre) {
    return nombre.toFixed(2).replace('.', ',') + ' €';
}

function calculer() {
    let prixMenu = 0;
    let reduction = 0;
    let livraison = 0;

    // on cache la distance si on est à Bordeaux
    const estBordeaux = champVille.value.trim().toLowerCase() === 'bordeaux';
    if (blocDistance) {
        blocDistance.style.display = estBordeaux ? 'none' : 'block';
    }

    const option = selectMenu.options[selectMenu.selectedIndex];
    if (option && option.value !== '') {
        const prix = parseFloat(option.dataset.prix);
        const min = parseInt(option.dataset.min);

        document.getElementById('aideMin').textContent = 'Minimum : ' + min + ' personnes';
        champNb.min = min;

        // si le champ est vide on met le minimum
        if (champNb.value === '' || parseInt(champNb.value) < min) {
            champNb.value = min;
        }

        const nb = parseInt(champNb.value);
        prixMenu = (prix / min) * nb;

        if (nb >= min + 5) {
            reduction = prixMenu * 0.1;
        }
    }

    if (!estBordeaux) {
        const distance = parseFloat(champDistance.value) || 0;
        livraison = 5 + 0.59 * distance;
    }

    const total = prixMenu - reduction + livraison;

    document.getElementById('prixMenu').textContent = formatEuro(prixMenu);
    document.getElementById('prixReduction').textContent = '- ' + formatEuro(reduction);
    document.getElementById('prixLivraison').textContent = formatEuro(livraison);
    document.getElementById('prixTotal').textContent = formatEuro(total);
}

selectMenu.addEventListener('change', calculer);
champNb.addEventListener('change', calculer);
champVille.addEventListener('input', calculer);
champDistance.addEventListener('input', calculer);

calculer();
