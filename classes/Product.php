<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $artisan_id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $stock_quantity;
    public $is_available;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT p.*, c.name as category_name, u.business_name as artisan_name,
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = true LIMIT 1) as primary_image
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN users u ON p.artisan_id = u.id
                 WHERE p.is_available = true
                 ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (artisan_id, category_id, name, description, price, stock_quantity, is_available)
                VALUES (:artisan_id, :category_id, :name, :description, :price, :stock_quantity, :is_available)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind values
        $stmt->bindParam(":artisan_id", $this->artisan_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":is_available", $this->is_available);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 