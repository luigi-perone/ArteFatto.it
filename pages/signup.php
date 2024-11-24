<?php 
require_once '../includes/header.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/dbconnect.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    
    try {
        $stmt = $db->prepare("INSERT INTO users (email, password_hash, first_name, last_name, role) 
                             VALUES (:email, :password, :first_name, :last_name, 'customer')");
        
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_role'] = 'customer';
            header("Location: /");
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23505) { // Codice per violazione unique constraint
            $error = "Email già registrata";
        } else {
            $error = "Errore durante la registrazione";
        }
    }
}
?>

<main class="container my-4">
    <div class="form-container">
        <h2 class="text-center mb-4">Registrati</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="first_name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            
            <div class="mb-3">
                <label for="last_name" class="form-label">Cognome</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       required minlength="8">
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Registrati</button>
            
            <div class="text-center mt-3">
                <p>Hai già un account? <a href="login.php">Accedi</a></p>
                <p>Sei un artigiano? <a href="signup-seller.php">Registrati come venditore</a></p>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?> 