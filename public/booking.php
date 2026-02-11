<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

// Tarkista kirjautuminen
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$serviceDurations = [
    "Hiustenleikkaus" => 30,
    "Parranleikkaus" => 15,
    "Koneajo" => 20,
    "Hiustenleikkaus + Parranleikkaus" => 45
];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tarkista CSRF-token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $service = $_POST['service'] ?? '';
        $date    = $_POST['date'] ?? '';
        $time    = $_POST['time'] ?? '';
        $notes   = trim($_POST['notes'] ?? '');
        $duration = $serviceDurations[$service] ?? 30;

        if (empty($service) || empty($date) || empty($time)) {
            $error = "Täytä kaikki pakolliset kentät.";
        } else {
            // Tarkista että varaus on tulevaisuudessa
            $bookingDateTime = new DateTime("$date $time");
            $now = new DateTime();
            
            if ($bookingDateTime <= $now) {
                $error = "Varaus tulee tehdä tulevaisuuteen.";
            } else {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO bookings (user_id, service, date, time, duration, notes)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $service,
                        $date,
                        $time,
                        $duration,
                        $notes
                    ]);
                    $success = "✅ Ajanvaraus onnistui!";
                } catch (Exception $e) {
                    $error = "Ajanvarausta ei voitu tallentaa. Yritä uudelleen.";
                }
            }
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
                <?php csrf_field(); ?>
                
                <label for="service">Valitse palvelu</label>
                <select id="service" name="service" required>
                    <option value="">-- Valitse palvelu --</option>
                    <?php foreach($serviceDurations as $s => $d): ?>
                        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?> - <?= $d ?> min</option>
                    <?php endforeach; ?>
                </select>

                <label for="date">Päivämäärä</label>
                <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required>

                <label for="available-times">Valitse aika</label>
                <div id="available-times" class="available-times"></div>
                <input type="hidden" id="time" name="time" required>

                <label for="notes">Lisätiedot</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Kirjoita lisätietoja..."></textarea>

                <button type="submit" class="btn-submit">Varaa aika</button>
            </form>
        </div>
    </section>
</main>

<script>
const dateInput = document.querySelector('#date');
const serviceInput = document.querySelector('#service');
const availableContainer = document.querySelector('#available-times');
const timeInput = document.querySelector('#time');

function fetchAvailableTimes() {
    const date = dateInput.value;
    const service = serviceInput.value;
    if(!service) return;

    fetch(`get_available_times.php?date=${date}&service=${encodeURIComponent(service)}`)
        .then(res => res.json())
        .then(times => {
            availableContainer.innerHTML = '';
            times.forEach(t => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = t;
                btn.classList.add('time-slot');
                btn.addEventListener('click', () => {
                    timeInput.value = t;
                    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                });
                availableContainer.appendChild(btn);
            });
        });
}

dateInput.addEventListener('change', fetchAvailableTimes);
serviceInput.addEventListener('change', fetchAvailableTimes);

fetchAvailableTimes();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>