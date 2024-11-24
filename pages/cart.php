<?php require_once '../includes/header.php'; ?>

<main class="container my-4">
    <h1>Il tuo carrello</h1>
    
    <div id="cart-items" class="my-4">
        <!-- Il contenuto del carrello verrà popolato dinamicamente via JavaScript -->
    </div>

    <div id="cart-summary" class="card">
        <div class="card-body">
            <h5 class="card-title">Riepilogo ordine</h5>
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotale:</span>
                <span id="subtotal">€0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span>IVA (22%):</span>
                <span id="tax">€0.00</span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
                <span>Totale:</span>
                <span id="total">€0.00</span>
            </div>
            <button id="checkout-button" class="btn btn-primary w-100 mt-3">Procedi all'acquisto</button>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function updateCartDisplay() {
        const cartItemsContainer = document.getElementById('cart-items');
        cartItemsContainer.innerHTML = '';
        
        if (cart.items.length === 0) {
            cartItemsContainer.innerHTML = '<p>Il carrello è vuoto</p>';
            return;
        }

        let subtotal = 0;
        
        cart.items.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="${item.primary_image || '/assets/images/placeholder.jpg'}" 
                             class="img-fluid" alt="${item.name}">
                    </div>
                    <div class="col-md-4">
                        <h5>${item.name}</h5>
                        <p class="text-muted">Artigiano: ${item.artisan_name}</p>
                    </div>
                    <div class="col-md-2">
                        €${item.price.toFixed(2)}
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control quantity-input" 
                               value="${item.quantity}" min="1"
                               onchange="cart.updateQuantity(${item.id}, this.value)">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger btn-sm" 
                                onclick="cart.removeItem(${item.id})">Rimuovi</button>
                    </div>
                </div>
            `;
            cartItemsContainer.appendChild(itemElement);
        });

        const tax = subtotal * 0.22;
        const total = subtotal + tax;

        document.getElementById('subtotal').textContent = `€${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `€${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `€${total.toFixed(2)}`;
    }

    updateCartDisplay();
    
    // Aggiorna il display quando il carrello cambia
    const originalUpdateCartUI = cart.updateCartUI;
    cart.updateCartUI = function() {
        originalUpdateCartUI.call(this);
        updateCartDisplay();
    };
});
</script>

<?php require_once '../includes/footer.php'; ?> 