<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Tarkista, että käyttäjä on kirjautunut
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = $_POST['service'] ?? '';
    $date    = $_POST['date'] ?? '';
    $time    = $_POST['time'] ?? '';
    $notes   = trim($_POST['notes'] ?? '');

    if (empty($service) || empty($date) || empty($time)) {
        $error = "Täytä kaikki pakolliset kentät.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, service, date, time, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $service,
                $date,
                $time,
                $notes
            ]);

            $success = "✅ Ajanvaraus onnistui!";
        } catch (Exception $e) {
            $error = "Ajanvarausta ei voitu tallentaa. Yritä uudelleen.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section id="booking">
        <div class="form-container">
            <h2>Varaa aika</h2>

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

            <form class="form" method="POST" action="booking.php">
                <label for="service">Valitse palvelu</label>
                <select id="service" name="service" required>
                    <option value="">-- Valitse palvelu --</option>
                    <option value="Hiustenleikkaus">Hiustenleikkaus - 25€</option>
                    <option value="Parranleikkaus">Parranleikkaus - 15€</option>
                    <option value="Koneajo">Koneajo - 20€</option>
                    <option value="Hiustenleikkaus + Parranleikkaus">Hiustenleikkaus + Parranleikkaus - 35€</option>
                </select>

                <label for="date">Päivämäärä</label>
                <input type="date" id="date" name="date" required>

                <label for="time">Aika</label>
                <input type="time" id="time" name="time" required>

                <label for="notes">Lisätiedot</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Kirjoita lisätietoja..."></textarea>

                <button type="submit" class="btn-submit">Varaa aika</button>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

