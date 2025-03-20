export class ProductSearch {
    constructor() {
        this.searchInput = document.getElementById('search-input');
        this.productsContainer = document.getElementById('products-container');
        this.loadingElement = document.getElementById('loading');
        this.debounceTimeout = null;
        this.currentPage = 1;
        
        this.init();
    }

    init() {
        this.searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        window.addEventListener('scroll', () => this.handleScroll());
    }

    handleSearch(searchTerm) {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(async () => {
            this.currentPage = 1;
            await this.searchProducts(searchTerm);
        }, 300);
    }

    async searchProducts(searchTerm) {
        try {
            this.showLoading();
            const response = await fetch(`../api/search.php?q=${encodeURIComponent(searchTerm)}&page=${this.currentPage}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                this.currentPage === 1 
                    ? this.renderProducts(data.data) 
                    : this.appendProducts(data.data);
            } else {
                this.showError('Ошибка загрузки данных');
            }
        } catch (error) {
            this.showError('Ошибка соединения');
        } finally {
            this.hideLoading();
        }
    }

    renderProducts(products) {
        this.productsContainer.innerHTML = products.length > 0
            ? this.generateProductCards(products)
            : this.getNoResultsTemplate();
    }

    generateProductCards(products) {
        return products.map(product => `
            <div class="product-card" data-id="${product.product_id}">
                ${this.getStatusBadge(product)}
                <img src="${product.image_url}" alt="${product.name}" class="product-image">
                <p class="product-category">${product.category_name}</p>
                <h3>${product.name}</h3>
                <div class="price-container">
                    ${this.getPriceHTML(product)}
                </div>
                <div class="actions">
                    <button class="btn-buy">Купить</button>
                </div>
            </div>
        `).join('');
    }

    getPriceHTML(product) {
        if (product.discount > 0) {
            return `
                <div class="price-content">
                    <span class="old-price">${product.price.toLocaleString()} ₽</span>
                    <span class="final-price">${product.final_price.toLocaleString()} ₽</span>
                </div>
            `;
        }
        return `<div class="price-content">${product.price.toLocaleString()} ₽</div>`;
    }

    getStatusBadge(product) {
        if (product.is_bestseller) return '<div class="status hit">Хит</div>';
        if (product.is_new) return '<div class="status new">Новинка</div>';
        return '';
    }

    getNoResultsTemplate() {
        return `
            <div class="no-results">
                <img src="../images/no-products.svg" alt="Ничего не найдено">
                <p>Товаров по вашему запросу не найдено</p>
            </div>
        `;
    }

    showLoading() {
        this.loadingElement.style.display = 'block';
    }

    hideLoading() {
        this.loadingElement.style.display = 'none';
    }

    showError(message) {
        this.productsContainer.innerHTML = `<div class="error">${message}</div>`;
    }

    handleScroll() {
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            this.currentPage++;
            this.searchProducts(this.searchInput.value);
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new ProductSearch();
});