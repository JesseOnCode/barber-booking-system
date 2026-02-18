<?php
/**
 * Kirjautumissivu
 *
 * Käyttäjä voi kirjautua sisään sähköpostilla ja salasanalla.
 * Admin-käyttäjät ohjataan admin-paneeliin, tavalliset käyttäjät varaussivulle.
 * Näyttää myös demo-tunnukset testausta varten.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$error = '';

// Tarkista rekisteröitymisen onnistuminen
$registrationSuccess = false;
if (isset($_SESSION['registration_success'])) {
    $registrationSuccess = true;
    unset($_SESSION['registration_success']);
}

/**
 * Käsittele kirjautumislomake
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tarkista CSRF-token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validoi kentät
        if (empty($email) || empty($password)) {
            $error = "Täytä kaikki kentät.";
        } else {
            // Hae käyttäjä tietokannasta
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tarkista salasana
            if ($user && password_verify($password, $user['password'])) {
                // Tallenna käyttäjätiedot sessioon
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Ohjaa käyttäjä oikealle sivulle
                if ($user['is_admin']) {
                    header("Location: admin/index.php");
                } else {
                    header("Location: booking.php");
                }
                exit;
            } else {
                $error = "Sähköposti tai salasana väärin.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Kirjaudu sisään</h1>

            <!-- Rekisteröitymisen onnistumisviesti -->
            <?php if($registrationSuccess): ?>
                <div class="form-messages">
                    <div class="form-success">
                        Rekisteröityminen onnistui! Voit nyt kirjautua sisään.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Virheviesti -->
            <?php if ($error): ?>
                <div class="form-messages">
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Demo-tunnukset testausta varten -->
            <div class="demo-credentials">
                <p><strong>Demo-tunnukset:</strong></p>
                <p><strong>Admin:</strong> admin@demo.com / password</p>
                <p><strong>Käyttäjä:</strong> käytä omia tunnuksia</p>
            </div>
            
            <!-- Kirjautumislomake -->
            <form class="form" method="POST" action="login.php">
                <?php csrf_field(); ?>
                
                <label for="email">Sähköposti</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="esimerkki@email.fi" 
                       required>

                <label for="password">Salasana</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Salasana" 
                       required>

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