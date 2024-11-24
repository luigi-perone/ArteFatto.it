// Cart functionality
const cart = {
    items: [],
    
    addItem(product) {
        const existingItem = this.items.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            this.items.push({...product, quantity: 1});
        }
        
        this.updateCartUI();
        this.saveCart();
    },
    
    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.updateCartUI();
        this.saveCart();
    },
    
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = parseInt(quantity);
            if (item.quantity <= 0) {
                this.removeItem(productId);
            }
        }
        this.updateCartUI();
        this.saveCart();
    },
    
    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    },
    
    loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            this.items = JSON.parse(savedCart);
            this.updateCartUI();
        }
    },
    
    updateCartUI() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const totalItems = this.items.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
        }
    }
};

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', () => {
    cart.loadCart();
});

// Search functionality
function initializeSearch() {
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchQuery = document.getElementById('search-input').value;
            const categoryFilter = document.getElementById('category-filter').value;
            
            // Construct search URL with parameters
            const searchParams = new URLSearchParams({
                q: searchQuery,
                category: categoryFilter
            });
            
            window.location.href = `/pages/search.php?${searchParams.toString()}`;
        });
    }
}

// Initialize search on page load
document.addEventListener('DOMContentLoaded', initializeSearch); 