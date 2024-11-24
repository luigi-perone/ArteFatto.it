<?php 
require_once '../includes/header.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/dbconnect.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: /");
            exit;
        }
    }
    
    $error = "Credenziali non valide";
}
?>

<main class="container my-4">
    <div class="form-container">
        <h2 class="text-center mb-4">Accedi</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Accedi</button>
            
            <div class="text-center mt-3">
                <p>Non hai un account? <a href="signup.php">Registrati</a></p>
                <p>Sei un artigiano? <a href="signup-seller.php">Registrati come venditore</a></p>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?> 