<?php
/**
 * Käyttäjäprofiilisivu
 * 
 * Näyttää käyttäjän tiedot ja mahdollistaa niiden muokkaamisen.
 * Vaatii kirjautumisen.
 * 
 * @package BarberShop
 * @author Jesse
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

// Tarkista että käyttäjä on kirjautunut
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Hae käyttäjän nykyiset tiedot
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Käsittele lomakkeen lähetys
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tarkista CSRF-token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        
        // Validointi
        if (empty($firstName) || empty($lastName)) {
            $error = "Etunimi ja sukunimi ovat pakollisia.";
        } else {
            // Päivitä tiedot
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?
                    WHERE id = ?
                ");
                $stmt->execute([$firstName, $lastName, $_SESSION['user_id']]);
                
                // Päivitä session
                $_SESSION['user_name'] = $firstName;
                
                $success = "✅ Tiedot päivitetty onnistuneesti!";
                
                // Päivitä user-muuttuja näyttämään uudet tiedot
                $user['first_name'] = $firstName;
                $user['last_name'] = $lastName;
                
            } catch (Exception $e) {
                if (APP_DEBUG) {
                    $error = "Tietojen päivitys epäonnistui: " . $e->getMessage();
                } else {
                    $error = "Tietojen päivitys epäonnistui. Yritä uudelleen.";
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
            <h1>Profiilisi</h1>
            
            <!-- Viestit -->
            <?php if($error): ?>
                <div class="form-messages">
                    <div class="form-error"><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="form-messages">
                    <div class="form-success"><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>
            
            <!-- Profiilitiedot -->
            <form class="form" method="POST" action="profile.php">
                <?php csrf_field(); ?>
                
                <label for="first_name">Etunimi *</label>
                <input type="text" 
                       id="first_name" 
                       name="first_name" 
                       value="<?= htmlspecialchars($user['first_name']) ?>" 
                       required>
                
                <label for="last_name">Sukunimi *</label>
                <input type="text" 
                       id="last_name" 
                       name="last_name" 
                       value="<?= htmlspecialchars($user['last_name']) ?>" 
                       required>
                
                <label for="email">Sähköposti</label>
                <input type="email" 
                       id="email" 
                       value="<?= htmlspecialchars($user['email']) ?>" 
                       disabled
                       style="opacity: 0.6; cursor: not-allowed;">
                <small style="color: #999; font-size: 12px;">Sähköpostia ei voi muuttaa</small>
                
                <button type="submit" class="btn-submit">Tallenna muutokset</button>
            </form>
            
            <p class="form-text">
                <a href="index.php">← Takaisin etusivulle</a>
            </p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>