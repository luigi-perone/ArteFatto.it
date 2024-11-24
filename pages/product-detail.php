<?php 
require_once '../includes/header.php';
require_once '../config/dbconnect.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Query principale per i dettagli del prodotto
$query = "SELECT p.*, 
          c.name as category_name,
          u.business_name as artisan_name,
          ap.description as artisan_description,
          ap.phone as artisan_phone,
          ap.website as artisan_website
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN users u ON p.artisan_id = u.id
          LEFT JOIN artisan_profiles ap ON u.id = ap.user_id
          WHERE p.id = :id AND p.is_available = true";

$stmt = $db->prepare($query);
$stmt->bindParam(":id", $_GET['id']);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /");
    exit;
}

// Query per le immagini del prodotto
$imageQuery = "SELECT * FROM product_images WHERE product_id = :id ORDER BY is_primary DESC";
$imageStmt = $db->prepare($imageQuery);
$imageStmt->bindParam(":id", $_GET['id']);
$imageStmt->execute();
$images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

// Query per le recensioni
$reviewQuery = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.customer_id = u.id 
                WHERE r.product_id = :id 
                ORDER BY r.created_at DESC";
$reviewStmt = $db->prepare($reviewQuery);
$reviewStmt->bindParam(":id", $_GET['id']);
$reviewStmt->execute();
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item">
                <a href="/pages/search.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($product['name']); ?>
            </li>
        </ol>
    </nav>

    <div class="row">
        <!-- Galleria immagini -->
        <div class="col-md-6 mb-4">
            <?php if (count($images) > 0): ?>
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     class="d-block w-100 product-detail-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <img src="/assets/images/placeholder.jpg" class="img-fluid" alt="Immagine non disponibile">
            <?php endif; ?>
        </div>

        <!-- Dettagli prodotto -->
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-muted">Categoria: <?php echo htmlspecialchars($product['category_name']); ?></p>
            
            <div class="price-section mb-4">
                <h2 class="text-primary">€<?php echo number_format($product['price'], 2); ?></h2>
                <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo $product['stock_quantity'] > 0 ? 'Disponibile' : 'Non disponibile'; ?>
                </span>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
                <div class="purchase-section mb-4">
                    <div class="input-group mb-3" style="max-width: 200px;">
                        <span class="input-group-text">Quantità</span>
                        <input type="number" class="form-control" id="quantity" value="1" min="1" 
                               max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <button class="btn btn-primary" onclick="addToCartWithQuantity(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        Aggiungi al carrello
                    </button>
                </div>
            <?php endif; ?>

            <div class="description-section mb-4">
                <h3>Descrizione</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Sezione Artigiano -->
            <div class="artisan-section card mb-4">
                <div class="card-body">
                    <h3>L'Artigiano</h3>
                    <h4><?php echo htmlspecialchars($product['artisan_name']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($product['artisan_description'])); ?></p>
                    <?php if ($product['artisan_website']): ?>
                        <a href="<?php echo htmlspecialchars($product['artisan_website']); ?>" 
                           class="btn btn-outline-primary" target="_blank">
                            Visita il sito web
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione Recensioni -->
    <section class="reviews-section mt-5">
        <h2>Recensioni</h2>
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                            </h5>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <small class="text-muted">
                            <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Nessuna recensione disponibile per questo prodotto.</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'customer'): ?>
            <div class="add-review-section mt-4">
                <h3>Scrivi una recensione</h3>
                <form action="/actions/add-review.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Valutazione</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Seleziona una valutazione</option>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> stelle</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Commento</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Invia recensione</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
function addToCartWithQuantity(product) {
    const quantity = parseInt(document.getElementById('quantity').value);
    if (quantity > 0 && quantity <= product.stock_quantity) {
        for (let i = 0; i < quantity; i++) {
            cart.addItem(product);
        }
        alert('Prodotto aggiunto al carrello!');
    } else {
        alert('Quantità non valida');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 