document.addEventListener('DOMContentLoaded', function() {
    // *** Mli page katsali chargement, katbda l'execution dyal les scripts

    const keywordInput = document.getElementById('search-keyword'); // *** L'input li katktbi fih smiya dyal service
    const searchButton = document.querySelector('.btn-search'); // *** Bouton rechercher
    const serviceBoxes = document.querySelectorAll('.service-box'); // *** Kol service (box) f page
    const categoryItems = document.querySelectorAll('.category-item'); // *** Les catégories (plomberie, jardinage...)
    const serviceSections = document.querySelectorAll('.service-section'); // *** Les sections li regroupo services par catégorie

    // *** Fonction katrja3 chi texte en minuscules w sans espaces zyada (utilité: comparaison)
    function normalize(str) {
        return str.trim().toLowerCase();
    }

    // *** Fonction katfiltri services selon chi mot clé (ex: "jardin")
    function filterServices() {
        const keyword = normalize(keywordInput.value); // *** Katchouf chno ketba user

        serviceBoxes.forEach(box => {
            const title = box.dataset.title; // *** Katjib le titre dyal service mn l'attribut "data-title"
            const matchesKeyword = !keyword || title.includes(keyword); // *** Katvérifi wach ltitre fih dak keyword
            box.style.display = matchesKeyword ? 'block' : 'none'; // *** Ila kayn => affichi, ila la => hide
        });
    }

    // *** Fonction li katfiltri services selon catégorie (ex: plomberie)
    function filterByCategory(category) {
        serviceSections.forEach(section => {
            const sectionCategory = section.dataset.category; // *** Katjib nom dyal catégorie (plomberie...)
            section.style.display = (category === 'all' || sectionCategory === category) ? 'block' : 'none'; // *** Kataffichi seulement had catégorie
        });

        keywordInput.value = ''; // *** Katvider input bach tb9a f coherence
        filterServices(); // *** Katredemarre la recherche b keyword (vide f had l7ala)
    }

    // *** Action mli user ydkhl mot clé w yclicki sur bouton recherche
    searchButton.addEventListener('click', filterServices);

    // *** Action mli user ytklli "Entrée" f clavier
    keywordInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // *** Katpreventi reload dyal page
            filterServices(); // *** Katfiltri les services
        }
    });

    // *** Katgoli l'interface: ila user dkliki 3la catégorie, filtri les services
    categoryItems.forEach(item => {
        item.addEventListener('click', () => {
            const category = item.dataset.category; // *** Katjib nom catégorie (min attribut data-category)
            filterByCategory(category); // *** Katdir filtrage selon had nom
        });
    });

    // *** Katpreventi mn l'effet de clic li kayji mn parent li fih service-card
    // *** C'est utile bach bouton li f dak card ykhdmo normal w maydirch clic sur toute la card
    document.querySelectorAll('.btn-edit, .btn-delete, .btn-favorite, .btn-demander, .btn-detail').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation(); // *** Katwa9af propagation dial clic vers parent
        });
    });
});
