<?php 
require_once '../includes/header.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/dbconnect.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Inserimento utente
        $stmt = $db->prepare("INSERT INTO users (email, password_hash, first_name, last_name, role) 
                             VALUES (:email, :password, :first_name, :last_name, 'artisan') 
                             RETURNING id");
        
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(":email", $_POST['email']);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":first_name", $_POST['first_name']);
        $stmt->bindParam(":last_name", $_POST['last_name']);
        
        $stmt->execute();
        $userId = $stmt->fetchColumn();
        
        // Inserimento profilo artigiano
        $stmt = $db->prepare("INSERT INTO artisan_profiles 
                             (user_id, business_name, description, phone, address, vat_number, website)
                             VALUES (:user_id, :business_name, :description, :phone, :address, :vat_number, :website)");
        
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":business_name", $_POST['business_name']);
        $stmt->bindParam(":description", $_POST['description']);
        $stmt->bindParam(":phone", $_POST['phone']);
        $stmt->bindParam(":address", $_POST['address']);
        $stmt->bindParam(":vat_number", $_POST['vat_number']);
        $stmt->bindParam(":website", $_POST['website']);
        
        $stmt->execute();
        
        $db->commit();
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'artisan';
        header("Location: /");
        exit;
        
    } catch (PDOException $e) {
        $db->rollBack();
        if ($e->getCode() == 23505) {
            $error = "Email già registrata";
        } else {
            $error = "Errore durante la registrazione";
        }
    }
}
?>

<main class="container my-4">
    <div class="form-container">
        <h2 class="text-center mb-4">Registrati come Artigiano</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <h4>Informazioni Personali</h4>
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
            
            <h4 class="mt-4">Informazioni Attività</h4>
            <div class="mb-3">
                <label for="business_name" class="form-label">Nome Attività</label>
                <input type="text" class="form-control" id="business_name" name="business_name" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Descrizione Attività</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Telefono</label>
                <input type="tel" class="form-control" id="phone" name="phone">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Indirizzo</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>
            
            <div class="mb-3">
                <label for="vat_number" class="form-label">Partita IVA</label>
                <input type="text" class="form-control" id="vat_number" name="vat_number" required>
            </div>
            
            <div class="mb-3">
                <label for="website" class="form-label">Sito Web</label>
                <input type="url" class="form-control" id="website" name="website">
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Registrati come Artigiano</button>
            
            <div class="text-center mt-3">
                <p>Hai già un account? <a href="login.php">Accedi</a></p>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?> 