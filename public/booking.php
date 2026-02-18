<?php
/**
 * Ajanvaraussivu
 *
 * Käyttäjä voi varata ajan valitsemalla:
 * - Palvelun (hiustenleikkaus, parranleikkaus, jne.)
 * - Päivämäärän
 * - Vapaan aikaslot:in
 * - Valinnaisia lisätietoja
 *
 * Tarkistaa että varaus tehdään tulevaisuuteen ja ettei aika ole jo varattu.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

// Tarkista että käyttäjä on kirjautunut
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/**
 * Palveluiden kestot minuutteina
 * Käytetään varauksen keston määrittämiseen
 */
$serviceDurations = [
    "Hiustenleikkaus" => 30,
    "Parranleikkaus" => 15,
    "Koneajo" => 20,
    "Hiustenleikkaus + Parranleikkaus" => 45
];

$success = '';
$error = '';

// Näytä kirjautumisonnistumisviesti (jos juuri kirjauduttu)
if (isset($_SESSION['login_success'])) {
    $success = "Tervetuloa, " . htmlspecialchars($_SESSION['user_name']) . "! Olet nyt kirjautunut sisään. Voit varata ajan alta.";
    unset($_SESSION['login_success']);
}

/**
 * Käsittele varauksen tallentaminen
 */
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

        // Validoi pakolliset kentät
        if (empty($service) || empty($date) || empty($time)) {
            $error = "Täytä kaikki pakolliset kentät.";
        } else {
            // Tarkista että varaus on tulevaisuudessa
            $bookingDateTime = new DateTime("$date $time");
            $now = new DateTime();
            
            if ($bookingDateTime <= $now) {
                $error = "Varaus tulee tehdä tulevaisuuteen.";
            } else {
                // Tallenna varaus tietokantaan
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
                    $success = "Ajanvaraus onnistui.";
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
    <section class="form-section">
        <div class="form-container">
            <h1>Varaa aika</h1>
            
            <!-- Onnistumisviesti -->
            <?php if ($success): ?>
                <div class="form-messages">
                    <div class="form-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Virheviesti -->
            <?php if ($error): ?>
                <div class="form-messages">
                    <div class="form-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Varauslomake -->
            <form class="form" method="POST" action="booking.php">
                <?php csrf_field(); ?>
                
                <!-- Palvelun valinta -->
                <label for="service">Valitse palvelu *</label>
                <select id="service" name="service" required>
                    <option value="">-- Valitse palvelu --</option>
                    <?php foreach($serviceDurations as $s => $d): ?>
                        <option value="<?= htmlspecialchars($s) ?>">
                            <?= htmlspecialchars($s) ?> - <?= $d ?> min
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Päivämäärän valinta -->
                <label for="date">Päivämäärä *</label>
                <input type="date" 
                       id="date" 
                       name="date" 
                       value="<?= date('Y-m-d') ?>" 
                       required>

                <!-- Vapaat aikaslotit (ladataan JavaScriptillä) -->
                <label for="available-times">Valitse aika *</label>
                <div id="available-times" class="available-times"></div>
                <input type="hidden" id="time" name="time" required>

                <!-- Lisätiedot (vapaaehtoinen) -->
                <label for="notes">Lisätiedot</label>
                <textarea id="notes" 
                          name="notes" 
                          rows="4" 
                          placeholder="Kirjoita lisätietoja..."></textarea>

                <button type="submit" class="btn-submit">Varaa aika</button>
            </form>

            <p class="form-text">
                <a href="profile.php">← Takaisin omaan profiiliin</a>
            </p>
        </div>
    </section>
</main>

<script>
/**
 * Dynaaminen vapaan ajan haku
 * 
 * Hakee vapaat ajat palvelimelta kun käyttäjä valitsee päivän tai palvelun.
 * Näyttää vapaat ajat klikattavina nappuloina.
 */

const dateInput = document.querySelector('#date');
const serviceInput = document.querySelector('#service');
const availableContainer = document.querySelector('#available-times');
const timeInput = document.querySelector('#time');

/**
 * Hae vapaat ajat palvelimelta
 */
function fetchAvailableTimes() {
    const date = dateInput.value;
    const service = serviceInput.value;
    
    // Ei haeta jos palvelua ei ole valittu
    if (!service) {
        availableContainer.innerHTML = '<p style="color: #999; text-align: center;">Valitse ensin palvelu</p>';
        return;
    }

    // Hae vapaat ajat API:sta
    fetch(`get_available_times.php?date=${date}&service=${encodeURIComponent(service)}`)
        .then(res => res.json())
        .then(times => {
            availableContainer.innerHTML = '';
            
            // Jos ei vapaita aikoja
            if (times.length === 0) {
                availableContainer.innerHTML = '<p style="color: #f44336; text-align: center;">Ei vapaita aikoja tälle päivälle</p>';
                return;
            }
            
            // Luo nappi jokaiselle vapaalle ajalle
            times.forEach(t => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = t;
                btn.classList.add('time-slot');
                
                // Valitse aika kun klikataan
                btn.addEventListener('click', () => {
                    timeInput.value = t;
                    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                });
                
                availableContainer.appendChild(btn);
            });
        })
        .catch(error => {
            console.error('Virhe vapaan ajan haussa:', error);
            availableContainer.innerHTML = '<p style="color: #f44336; text-align: center;">Virhe vapaan ajan haussa</p>';
        });
}

// Kuuntele muutoksia ja hae vapaat ajat
dateInput.addEventListener('change', fetchAvailableTimes);
serviceInput.addEventListener('change', fetchAvailableTimes);

// Hae vapaat ajat heti sivun latautuessa
fetchAvailableTimes();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>