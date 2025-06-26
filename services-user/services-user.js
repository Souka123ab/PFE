// 🌟 Mlli l page katsali tchargi (DOM), katbda l code
document.addEventListener('DOMContentLoaded', function () {

    // 🌟 Katjib les éléments li f page
    const keywordInput = document.getElementById('search-keyword'); // 🔍 input dyal l keyword (mot-clé)
    const searchButton = document.querySelector('.btn-search'); // 🔘 bouton dyal la recherche
    const serviceBoxes = document.querySelectorAll('.service-box'); // 📦 kol box dyal service
    const categoryItems = document.querySelectorAll('.category-item'); // 📂 les boutons dyal les catégories
    const serviceSections = document.querySelectorAll('.service-section'); // 🧩 les sections li fihom services regroupés b catégorie

    // 🌟 Fonction katsayb string : kats7ab les espaces w katdir lowercase
    function normalize(str) {
        return str.trim().toLowerCase();
    }

    // 🌟 Fonction li katfiltri services b mot-clé (keyword)
    function filterServices() {
        const keyword = keywordInput ? normalize(keywordInput.value) : ''; // 💬 katsayb l mot-clé

        serviceBoxes.forEach(box => {
            const title = box.dataset.title || ''; // 🏷️ katjib l title mn data-title
            const matchesKeyword = !keyword || title.includes(keyword); // ✔️ katsheki wach l title fih keyword
            box.style.display = matchesKeyword ? 'block' : 'none'; // 👁️ ila kayn kayban, ila ma kaynch kayt7aja
        });
    }

    // 🌟 Fonction li katfiltri services b catégorie
    function filterByCategory(category) {
        serviceSections.forEach(section => {
            const sectionCategory = section.dataset.category; // 📂 katjib catégorie dyal section
            section.style.display = (category === 'all' || sectionCategory === category) ? 'block' : 'none'; // 👁️ kayaffichi ghir li katmatchi
        });

        // 🔄 kinmas7o l keyword m input
        if (keywordInput) keywordInput.value = '';

        // 🔁 kankamlo b filter dyal keyword (par sécurité)
        filterServices();
    }

    // 🌟 Mlli l user ydkhl click 3la bouton recherche
    if (searchButton) {
        searchButton.addEventListener('click', filterServices);
    }

    // 🌟 Mlli l user ydkhl "Entrée" f champ dyal keyword
    if (keywordInput) {
        keywordInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // ❌ ma kaykhalich l form itsift
                filterServices();   // 🔍 kaydir recherche b keyword
            }
        });
    }

    // 🌟 Kol bouton catégorie kaydir filtrage 3la click
    categoryItems.forEach(item => {
        item.addEventListener('click', () => {
            const category = item.dataset.category; // 📂 katjib catégorie

            // ✨ Katdir mise à jour de la classe active
            categoryItems.forEach(ci => ci.classList.remove('active'));
            item.classList.add('active');

            // 📛 katfiltri b had catégorie
            filterByCategory(category);
        });
    });

    // 🌟 Mlli ykoun click f chi bouton (edit, delete...), ma kaydirch chi action f parent
    document.querySelectorAll('.btn-edit, .btn-delete, .btn-favorite, .btn-demander, .btn-detail').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation(); // 🛑 kaywa9af propagation dyal click
        });
    });

    // 🌟 par défaut, katklik 3la première catégorie bach ybanou les services
    if (categoryItems.length > 0) {
        categoryItems[0].click(); // 🔁 kifach katbda l page, kayaffichi la première catégorie
    }
});
