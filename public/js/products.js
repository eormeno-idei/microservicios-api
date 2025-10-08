/**
 * Products Catalog JavaScript
 * Provides filtering and search functionality for the products catalog
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const stockFilter = document.getElementById('stockFilter');
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');

    // Initialize the products catalog
    initializeProductsCatalog();

    /**
     * Initialize the products catalog functionality
     */
    function initializeProductsCatalog() {
        addEventListeners();
        addAnimationStyles();
        enableSmoothScrolling();
    }

    /**
     * Add event listeners for filtering
     */
    function addEventListeners() {
        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        stockFilter.addEventListener('change', filterProducts);
    }

    /**
     * Filter products based on search term, category, and stock status
     */
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedStock = stockFilter.value;

        let visibleCount = 0;

        productCards.forEach(card => {
            const name = card.dataset.name;
            const category = card.dataset.category;
            const stock = parseInt(card.dataset.stock);

            const matchesSearch = name.includes(searchTerm);
            const matchesCategory = selectedCategory === '' || category === selectedCategory;
            const matchesStock = checkStockFilter(stock, selectedStock);

            if (matchesSearch && matchesCategory && matchesStock) {
                showProduct(card);
                visibleCount++;
            } else {
                hideProduct(card);
            }
        });

        toggleNoResultsMessage(visibleCount);
    }

    /**
     * Check if product matches stock filter
     * @param {number} stock - Product stock amount
     * @param {string} filter - Stock filter value
     * @returns {boolean}
     */
    function checkStockFilter(stock, filter) {
        switch (filter) {
            case 'available':
                return stock > 10;
            case 'low':
                return stock > 0 && stock <= 10;
            case 'out':
                return stock === 0;
            default:
                return true;
        }
    }

    /**
     * Show a product card with animation
     * @param {HTMLElement} card - Product card element
     */
    function showProduct(card) {
        card.style.display = 'block';
        card.style.animation = 'fadeIn 0.3s ease';
    }

    /**
     * Hide a product card
     * @param {HTMLElement} card - Product card element
     */
    function hideProduct(card) {
        card.style.display = 'none';
    }

    /**
     * Show or hide the no results message
     * @param {number} visibleCount - Number of visible products
     */
    function toggleNoResultsMessage(visibleCount) {
        let noResultsMsg = document.getElementById('noResults');

        if (visibleCount === 0 && productCards.length > 0) {
            if (!noResultsMsg) {
                noResultsMsg = createNoResultsMessage();
                productsGrid.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }

    /**
     * Create the no results message element
     * @returns {HTMLElement}
     */
    function createNoResultsMessage() {
        const noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'noResults';
        noResultsMsg.className = 'no-products';
        noResultsMsg.innerHTML = `
            <div class="no-products-icon">🔍</div>
            <h3>No se encontraron productos</h3>
            <p>Intenta con otros filtros de búsqueda</p>
        `;
        return noResultsMsg;
    }

    /**
     * Add animation styles to the document
     */
    function addAnimationStyles() {
        if (!document.getElementById('productsAnimationStyles')) {
            const style = document.createElement('style');
            style.id = 'productsAnimationStyles';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Enable smooth scroll behavior
     */
    function enableSmoothScrolling() {
        document.documentElement.style.scrollBehavior = 'smooth';
    }

    // Public API for external access if needed
    window.ProductsCatalog = {
        filterProducts: filterProducts,
        resetFilters: function() {
            searchInput.value = '';
            categoryFilter.value = '';
            stockFilter.value = '';
            filterProducts();
        }
    };
});
