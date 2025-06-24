document.addEventListener('DOMContentLoaded', () => {
    // Search functionality (existing code)
    const searchBtn = document.querySelector('.btn-search');
    const searchInput = document.querySelector('.search-input input');
    const locationInput = document.querySelector('.location-input input');
    
    searchBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        const location = locationInput.value.trim();
        if (searchTerm || location) {
            performSearch(searchTerm, location);
        }
    });

    [searchInput, locationInput].forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = searchInput.value.trim();
                const location = locationInput.value.trim();
                performSearch(searchTerm, location);
            }
        });
    });

    // Category click handlers
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.addEventListener('click', function() {
            const categoryName = this.querySelector('span').textContent;
            filterByCategory(categoryName);
        });
    });

    // Service card hover effects
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // View more links
    const viewMoreLinks = document.querySelectorAll('.view-more');
    viewMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionTitle = this.closest('.section-header').querySelector('h3').textContent;
            loadMoreServices(sectionTitle);
        });
    });

    // Mobile menu toggle (if needed)
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('active');
        });
    }

    // Search, filter, load more, and notification functions (unchanged)
    function performSearch(searchTerm, location) {
        console.log('Searching for:', searchTerm, 'in location:', location);
        showLoadingState();
        setTimeout(() => {
            filterServices(searchTerm, location);
            hideLoadingState();
        }, 1000);
    }

    function filterByCategory(categoryName) {
        console.log('Filtering by category:', categoryName);
        const sections = document.querySelectorAll('.service-section');
        sections.forEach(section => {
            const sectionTitle = section.querySelector('h3').textContent.toLowerCase();
            if (sectionTitle.includes(categoryName.toLowerCase()) || categoryName === 'Voir plus') {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
        const firstVisibleSection = document.querySelector('.service-section[style="display: block;"], .service-section:not([style])');
        if (firstVisibleSection) {
            firstVisibleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function filterServices(searchTerm, location) {
        const serviceCards = document.querySelectorAll('.service-card');
        let visibleCount = 0;
        serviceCards.forEach(card => {
            const description = card.querySelector('.service-description').textContent.toLowerCase();
            const providerName = card.querySelector('.provider-info h4').textContent.toLowerCase();
            const matchesSearch = !searchTerm || description.includes(searchTerm.toLowerCase()) || providerName.includes(searchTerm.toLowerCase());
            const matchesLocation = !location;
            if (matchesSearch && matchesLocation) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        const sections = document.querySelectorAll('.service-section');
        sections.forEach(section => {
            const visibleCards = section.querySelectorAll('.service-card[style="display: block;"], .service-card:not([style])');
            if (visibleCards.length === 0) {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
        showSearchResults(visibleCount, searchTerm, location);
    }

    function loadMoreServices(sectionTitle) {
        console.log('Loading more services for:', sectionTitle);
        const section = Array.from(document.querySelectorAll('.service-section')).find(s => s.querySelector('h3').textContent.includes(sectionTitle.split(' ').pop()));
        if (section) {
            const grid = section.querySelector('.services-grid');
            showNotification('Plus de services chargés avec succès!');
        }
    }

    function showLoadingState() {
        const searchBtn = document.querySelector('.btn-search');
        const originalText = searchBtn.innerHTML;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche...';
        searchBtn.disabled = true;
        searchBtn.dataset.originalText = originalText;
    }

    function hideLoadingState() {
        const searchBtn = document.querySelector('.btn-search');
        searchBtn.innerHTML = searchBtn.dataset.originalText;
        searchBtn.disabled = false;
    }

    function showSearchResults(count, searchTerm, location) {
        const existingMessage = document.querySelector('.search-results-message');
        if (existingMessage) existingMessage.remove();
        const message = document.createElement('div');
        message.className = 'search-results-message';
        message.style.cssText = 'background: #e3f2fd; padding: 1rem; margin: 2rem 0; border-radius: 8px; text-align: center; color: #1976d2;';
        let messageText = `${count} service(s) trouvé(s)`;
        if (searchTerm) messageText += ` pour "${searchTerm}"`;
        if (location) messageText += ` à ${location}`;
        message.textContent = messageText;
        const mainContent = document.querySelector('.main-content');
        mainContent.insertBefore(message, mainContent.firstChild);
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4caf50; color: white; padding: 1rem 2rem; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 1000; transform: translateX(100%); transition: transform 0.3s ease;';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.style.transform = 'translateX(0)', 100);
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Scroll-to-top functionality
    window.addEventListener('scroll', function() {
        const scrollBtn = document.querySelector('.scroll-to-top');
        if (window.pageYOffset > 300) {
            if (!scrollBtn) createScrollToTopButton();
            else scrollBtn.style.display = 'block';
        } else if (scrollBtn) scrollBtn.style.display = 'none';
    });

    function createScrollToTopButton() {
        const scrollBtn = document.createElement('button');
        scrollBtn.className = 'scroll-to-top';
        scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        scrollBtn.style.cssText = 'position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; border: none; border-radius: 50%; background: #20b2aa; color: white; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.2); z-index: 1000; transition: all 0.3s ease;';
        scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        scrollBtn.addEventListener('mouseenter', () => { scrollBtn.style.background = '#1a9b94'; scrollBtn.style.transform = 'scale(1.1)'; });
        scrollBtn.addEventListener('mouseleave', () => { scrollBtn.style.background = '#20b2aa'; scrollBtn.style.transform = 'scale(1)'; });
        document.body.appendChild(scrollBtn);
    }

    // Get all "Demander" and "Favorite" buttons
    const demanderButtons = document.querySelectorAll('.btn-demander');
    const favoriteButtons = document.querySelectorAll('.btn-favorite');

    // Add click event listener to "Demander" buttons
    demanderButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default behavior if inside a link
            const href = button.getAttribute('data-href');
            const serviceCard = button.closest('.service-card');
            const providerName = serviceCard.querySelector('h4').textContent;
            const price = serviceCard.querySelector('.price').textContent;
            const description = serviceCard.querySelector('.service-description').textContent;
            const phone = serviceCard.closest('a').href.match(/phone=([^&]+)/)[1]; // Extract phone from URL
            window.location.href = `${href}?provider_name=${encodeURIComponent(providerName)}&price=${encodeURIComponent(price)}&description=${encodeURIComponent(description)}&phone=${encodeURIComponent(phone)}`;
        });
    });

    // Add click event listener to "Favorite" buttons
    favoriteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default behavior if inside a link
            const href = button.getAttribute('data-href');
            const serviceCard = button.closest('.service-card');
            const providerName = serviceCard.querySelector('h4').textContent;
            const price = serviceCard.querySelector('.price').textContent;
            const description = serviceCard.querySelector('.service-description').textContent;
            const phone = serviceCard.closest('a').href.match(/phone=([^&]+)/)[1]; // Extract phone from URL
            window.location.href = `${href}?provider_name=${encodeURIComponent(providerName)}&price=${encodeURIComponent(price)}&description=${encodeURIComponent(description)}&phone=${encodeURIComponent(phone)}`;
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.querySelector('.search-btn');
    const serviceInput = document.querySelector('.search-inputs input[placeholder*="service"]');
    const locationInput = document.querySelector('.search-inputs input[placeholder*="ville"]');

    searchBtn.addEventListener('click', () => {
        const service = serviceInput.value.toLowerCase();
        const location = locationInput.value.toLowerCase();

        const providers = document.querySelectorAll('.provider-card');

        providers.forEach(provider => {
            const badge = provider.querySelector('.service-badge')?.textContent.toLowerCase();
            const city = provider.querySelector('.location span')?.textContent.toLowerCase();

            if ((service && !badge.includes(service)) || (location && !city.includes(location))) {
                provider.style.display = 'none';
            } else {
                provider.style.display = 'block';
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const demanderButtons = document.querySelectorAll('.btn-demander');
    demanderButtons.forEach(button => {
        button.addEventListener('click', () => {
            const href = button.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
document.addEventListener("DOMContentLoaded", () => {
    // Handle Demander button clicks
    document.querySelectorAll('.btn-demander').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent any default action
            const href = button.getAttribute('data-href');
            if (href) {
                window.location.href = href; // Navigate to demander.php
            }
        });
    });

    // Optional: Handle other interactions (e.g., search, filter) if needed
});
document.querySelectorAll('.btn-favorite, .btn-demander').forEach(button => {
    button.addEventListener('click', () => {
        window.location.href = button.getAttribute('data-href');
    });
});