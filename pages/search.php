<?php 
require_once '../includes/header.php';
require_once '../config/dbconnect.php';
require_once '../classes/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$search = isset($_GET['q']) ? $_GET['q'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Query per le categorie
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query per i prodotti filtrati
$query = "SELECT p.*, c.name as category_name, u.business_name as artisan_name,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = true LIMIT 1) as primary_image
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN users u ON p.artisan_id = u.id
          WHERE p.is_available = true";

if ($search) {
    $query .= " AND (p.name ILIKE :search OR p.description ILIKE :search)";
}
if ($category) {
    $query .= " AND p.category_id = :category";
}

$stmt = $db->prepare($query);
if ($search) {
    $searchTerm = "%$search%";
    $stmt->bindParam(":search", $searchTerm);
}
if ($category) {
    $stmt->bindParam(":category", $category);
}
$stmt->execute();
?>

<main class="container my-4">
    <div class="row">
        <!-- Filtri di ricerca -->
        <div class="col-md-3">
            <div class="search-filters">
                <h4>Filtri</h4>
                <form id="search-form" method="GET">
                    <div class="mb-3">
                        <label for="search-input" class="form-label">Cerca</label>
                        <input type="text" class="form-control" id="search-input" name="q" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="category-filter" class="form-label">Categoria</label>
                        <select class="form-select" id="category-filter" name="category">
                            <option value="">Tutte le categorie</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Applica filtri</button>
                </form>
            </div>
        </div>

        <!-- Risultati della ricerca -->
        <div class="col-md-9">
            <h2>Risultati della ricerca</h2>
            <div class="row">
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
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
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?> 