<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tarkista CSRF-token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Täytä kaikki kentät.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['login_success'] = true; // Aseta onnistumisviesti

                header("Location: booking.php");
                exit;
            } else {
                $error = "Sähköposti tai salasana väärin.";
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="assets/css/main.css">

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Kirjaudu sisään</h1>

            <?php if ($error): ?>
                <div class="form-messages">
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form class="form" method="POST" action="login.php">
                <?php csrf_field(); ?>
                
                <label for="email">Sähköposti</label>
                <input type="email" id="email" name="email" placeholder="Sähköposti" required>

                <label for="password">Salasana</label>
                <input type="password" id="password" name="password" placeholder="Salasana" required>

                <button type="submit" class="btn-submit">Kirjaudu</button>
            </form>

            <p class="form-text">
                Eikö sinulla ole vielä tunnusta? 
                <a href="register.php">Rekisteröidy tästä</a>
            </p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
