<?php 
require_once '../includes/header.php';
require_once '../config/dbconnect.php';
require_once '../classes/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$result = $product->read();
?>

<main class="container my-4">
    <section class="hero-section text-center py-5 bg-light">
        <h1>Benvenuto nel Marketplace degli Artigiani Italiani</h1>
        <p class="lead">Scopri prodotti unici realizzati dai migliori artigiani d'Italia</p>
        
        <div class="cart-button-container mb-3">
            <a href="cart.php" class="btn btn-outline-primary">
                <i class="fas fa-shopping-cart"></i> Carrello
                <span id="cart-count" class="badge bg-primary">0</span>
            </a>
        </div>

        <form id="search-form" class="mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="search-input" class="form-control" placeholder="Cerca prodotti...">
                        <button class="btn btn-primary" type="submit">Cerca</button>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <section class="featured-products my-5">
        <h2 class="text-center mb-4">Prodotti in Evidenza</h2>
        <div class="row">
            <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['primary_image'] ?? '/assets/images/placeholder.jpg'); ?>" 
                             class="product-image" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="price">€<?php echo number_format($row['price'], 2); ?></p>
                            <button class="btn btn-primary" onclick="cart.addItem(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                Aggiungi al carrello
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?> 