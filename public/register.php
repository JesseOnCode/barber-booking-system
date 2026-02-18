<?php
/**
 * Rekisteröitymissivu
 *
 * Uusi käyttäjä voi luoda tunnuksen antamalla:
 * - Etunimen ja sukunimen
 * - Sähköpostiosoitteen
 * - Salasanan (vahvistetaan kahdesti)
 *
 * Tarkistaa että sähköposti ei ole jo käytössä.
 * Onnistuneen rekisteröitymisen jälkeen ohjaa kirjautumissivulle.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$error = '';

/**
 * Käsittele rekisteröitymislomake
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tarkista CSRF-token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validoi pakolliset kentät
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $error = "Täytä kaikki kentät.";
        } 
        // Tarkista että salasanat täsmäävät
        elseif ($password !== $confirm) {
            $error = "Salasanat eivät täsmää.";
        }
        // Tarkista salasanan pituus
        elseif (strlen($password) < 8) {
            $error = "Salasanan tulee olla vähintään 8 merkkiä.";
        }
        // Validoi sähköpostiosoite
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Virheellinen sähköpostiosoite.";
        } 
        else {
            // Tarkista ettei sähköposti ole jo käytössä
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Sähköposti on jo käytössä.";
            } else {
                // Hashaa salasana ja tallenna käyttäjä
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO users (first_name, last_name, email, password)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
                    
                    // Ohjaa kirjautumissivulle onnistumisviestillä
                    $_SESSION['registration_success'] = true;
                    header("Location: login.php");
                    exit;
                } catch (Exception $e) {
                    $error = "Rekisteröityminen epäonnistui. Yritä uudelleen.";
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Rekisteröidy</h1>

            <!-- Virheviesti -->
            <?php if($error): ?>
                <div class="form-messages">
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Rekisteröitymislomake -->
            <form class="form" method="POST" action="register.php">
                <?php csrf_field(); ?>
                
                <label for="first_name">Etunimi *</label>
                <input type="text" 
                       id="first_name" 
                       name="first_name" 
                       placeholder="Etunimi" 
                       required>

                <label for="last_name">Sukunimi *</label>
                <input type="text" 
                       id="last_name" 
                       name="last_name" 
                       placeholder="Sukunimi" 
                       required>

                <label for="email">Sähköposti *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="esimerkki@email.fi" 
                       required>

                <label for="password">Salasana * (vähintään 8 merkkiä)</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Salasana" 
                       minlength="8"
                       required>

                <label for="password_confirm">Vahvista salasana *</label>
                <input type="password" 
                       id="password_confirm" 
                       name="confirm_password" 
                       placeholder="Vahvista salasana" 
                       minlength="8"
                       required>

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