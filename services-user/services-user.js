
document.addEventListener('DOMContentLoaded', function() {
    const keywordInput = document.getElementById('search-keyword');
    const searchButton = document.querySelector('.btn-search');
    const serviceBoxes = document.querySelectorAll('.service-box');
    const categoryItems = document.querySelectorAll('.category-item');
    const serviceSections = document.querySelectorAll('.service-section');

    function normalize(str) {
        return str.trim().toLowerCase();
    }

    function filterServices() {
        const keyword = normalize(keywordInput.value);

        serviceBoxes.forEach(box => {
            const title = box.dataset.title;
            const matchesKeyword = !keyword || title.includes(keyword);
            box.style.display = matchesKeyword ? 'block' : 'none';
        });
    }

    function filterByCategory(category) {
        serviceSections.forEach(section => {
            const sectionCategory = section.dataset.category;
            section.style.display = (category === 'all' || sectionCategory === category) ? 'block' : 'none';
        });

        keywordInput.value = '';
        filterServices(); // Pour réinitialiser les services visibles dans les sections affichées
    }

    // Recherche par mot-clé
    searchButton.addEventListener('click', filterServices);

    // Recherche sur "Entrée"
    keywordInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterServices();
        }
    });

    // Clic sur une catégorie
    categoryItems.forEach(item => {
        item.addEventListener('click', () => {
            const category = item.dataset.category;
            filterByCategory(category);
        });
    });

    // Empêcher les boutons de naviguer ailleurs à cause du lien parent
    document.querySelectorAll('.btn-edit, .btn-delete, .btn-favorite, .btn-demander, .btn-detail').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation(); // Bloque l'effet de clic du parent
        });
    });
});

