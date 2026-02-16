<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Backend: käsittele lomake
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // Perusvalidointi
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = "Täytä kaikki kentät.";
    } elseif ($password !== $confirm) {
        $error = "Salasanat eivät täsmää.";
    } else {
        // Tarkista, ettei sähköposti ole jo käytössä
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Sähköposti on jo käytössä.";
        } else {
            // Hashaa salasana ja tallenna käyttäjä
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
            
            // Ohjaa login-sivulle onnistumisviestillä
            $_SESSION['registration_success'] = true;
            header("Location: login.php");
            exit;
        }
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="assets/css/main.css">

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Rekisteröidy</h1>

            <!-- Viestikontti -->
            <?php if($error): ?>
                <div class="form-messages">
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form class="form" method="POST" action="register.php">
                <label for="first_name">Etunimi</label>
                <input type="text" id="first_name" name="first_name" placeholder="Etunimesi" required>

                <label for="last_name">Sukunimi</label>
                <input type="text" id="last_name" name="last_name" placeholder="Sukunimesi" required>

                <label for="email">Sähköposti</label>
                <input type="email" id="email" name="email" placeholder="Sähköposti" required>

                <label for="password">Salasana</label>
                <input type="password" id="password" name="password" placeholder="Salasana" required>

                <label for="password_confirm">Vahvista salasana</label>
                <input type="password" id="password_confirm" name="confirm_password" placeholder="Vahvista salasana" required>

                <button type="submit" class="btn-submit">Rekisteröidy</button>
            </form>

            <p class="form-text">
                Onko sinulla jo tili? 
                <a href="login.php">Kirjaudu tästä</a>
            </p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>