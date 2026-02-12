<?php
/**
 * Käyttäjän profiili ja varaukset
 * 
 * Yhdistetty sivu jossa käyttäjä voi:
 * - Muokata tietojaan
 * - Nähdä varaukset
 * - Peruuttaa varauksia
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

// Käsittele profiilin päivitys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        
        if (empty($firstName) || empty($lastName)) {
            $error = "Etunimi ja sukunimi ovat pakollisia.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?
                    WHERE id = ?
                ");
                $stmt->execute([$firstName, $lastName, $_SESSION['user_id']]);
                
                $_SESSION['user_name'] = $firstName;
                $success = "✅ Tiedot päivitetty onnistuneesti!";
                
                $user['first_name'] = $firstName;
                $user['last_name'] = $lastName;
                
            } catch (Exception $e) {
                $error = "Tietojen päivitys epäonnistui. Yritä uudelleen.";
            }
        }
    }
}

// Käsittele varauksen peruutus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake. Yritä uudelleen.";
    } else {
        $bookingId = (int)$_POST['booking_id'];
        
        $stmt = $pdo->prepare("
            SELECT * FROM bookings 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        $stmt->execute([$bookingId, $_SESSION['user_id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            $bookingDateTime = new DateTime($booking['date'] . ' ' . $booking['time']);
            $now = new DateTime();
            
            if ($bookingDateTime > $now) {
                $stmt = $pdo->prepare("
                    UPDATE bookings 
                    SET status = 'cancelled' 
                    WHERE id = ?
                ");
                $stmt->execute([$bookingId]);
                
                $success = "✅ Varaus peruutettu onnistuneesti.";
            } else {
                $error = "Et voi peruuttaa mennyttä varausta.";
            }
        } else {
            $error = "Varausta ei löytynyt tai se on jo peruutettu.";
        }
    }
}

// Hae käyttäjän varaukset
$stmt = $pdo->prepare("
    SELECT * FROM bookings 
    WHERE user_id = ? 
    ORDER BY date DESC, time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jaa varaukset tuleviin ja menneisiin
$upcomingBookings = [];
$pastBookings = [];
$now = new DateTime();

foreach ($bookings as $booking) {
    $bookingDateTime = new DateTime($booking['date'] . ' ' . $booking['time']);
    
    if ($bookingDateTime > $now && $booking['status'] !== 'cancelled') {
        $upcomingBookings[] = $booking;
    } else {
        $pastBookings[] = $booking;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="form-section">
        <div class="profile-dashboard">
            <h1>Oma profiili</h1>
            
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
            
            <!-- Välilehdet -->
            <div class="tabs">
                <button class="tab-button active" onclick="openTab(event, 'info')">
                    👤 Tietoni
                </button>
                <button class="tab-button" onclick="openTab(event, 'bookings')">
                    📅 Varaukseni (<?= count($upcomingBookings) ?>)
                </button>
                <button class="tab-button" onclick="openTab(event, 'history')">
                    📋 Historia (<?= count($pastBookings) ?>)
                </button>
            </div>
            
            <!-- Välilehti 1: Omat tiedot -->
            <div id="info" class="tab-content active">
                <h2>Omat tiedot</h2>
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
                    
                    <button type="submit" name="update_profile" class="btn-submit">Tallenna muutokset</button>
                </form>
            </div>
            
            <!-- Välilehti 2: Tulevat varaukset -->
            <div id="bookings" class="tab-content">
                <h2>Tulevat varaukset</h2>
                
                <?php if (empty($upcomingBookings)): ?>
                    <div class="no-bookings">
                        <p>📅 Sinulla ei ole tulevia varauksia.</p>
                        <a href="booking.php" class="btn-primary">Varaa aika nyt</a>
                    </div>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach($upcomingBookings as $booking): ?>
                            <div class="booking-card upcoming">
                                <div class="booking-header">
                                    <h3><?= htmlspecialchars($booking['service']) ?></h3>
                                    <span class="status-badge status-pending">Vahvistettu</span>
                                </div>
                                
                                <div class="booking-details">
                                    <p><strong>📅 Päivä:</strong> <?= date('d.m.Y', strtotime($booking['date'])) ?></p>
                                    <p><strong>🕐 Aika:</strong> <?= date('H:i', strtotime($booking['time'])) ?></p>
                                    <p><strong>⏱️ Kesto:</strong> <?= $booking['duration'] ?> min</p>
                                    
                                    <?php if ($booking['notes']): ?>
                                        <p><strong>📝 Lisätiedot:</strong> <?= htmlspecialchars($booking['notes']) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="booking-actions">
                                    <form method="POST" style="display: inline;">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <button type="submit" 
                                                name="cancel_booking" 
                                                class="btn-cancel"
                                                onclick="return confirm('Haluatko varmasti peruuttaa tämän varauksen?')">
                                            ❌ Peruuta varaus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Välilehti 3: Historia -->
            <div id="history" class="tab-content">
                <h2>Varaushistoria</h2>
                
                <?php if (empty($pastBookings)): ?>
                    <div class="no-bookings">
                        <p>📋 Ei aiempia varauksia.</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach($pastBookings as $booking): ?>
                            <div class="booking-card past">
                                <div class="booking-header">
                                    <h3><?= htmlspecialchars($booking['service']) ?></h3>
                                    <span class="status-badge status-<?= $booking['status'] ?>">
                                        <?php
                                        $statuses = [
                                            'pending' => 'Vahvistettu',
                                            'confirmed' => 'Vahvistettu',
                                            'cancelled' => 'Peruutettu',
                                            'completed' => 'Suoritettu'
                                        ];
                                        echo $statuses[$booking['status']] ?? ucfirst($booking['status']);
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="booking-details">
                                    <p><strong>📅 Päivä:</strong> <?= date('d.m.Y', strtotime($booking['date'])) ?></p>
                                    <p><strong>🕐 Aika:</strong> <?= date('H:i', strtotime($booking['time'])) ?></p>
                                    <p><strong>⏱️ Kesto:</strong> <?= $booking['duration'] ?> min</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <p class="form-text" style="margin-top: 30px; text-align: center;">
                <a href="index.php">← Takaisin etusivulle</a> | 
                <a href="booking.php">📅 Varaa uusi aika</a>
            </p>
        </div>
    </section>
</main>

<!-- Välilehtien JavaScript -->
<script>
function openTab(evt, tabName) {
    // Piilota kaikki välilehdet
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Poista "active" kaikista napeista
    const tabButtons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }
    
    // Näytä valittu välilehti
    document.getElementById(tabName).classList.add('active');
    evt.currentTarget.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>