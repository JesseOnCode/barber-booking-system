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
$passwordSuccess = '';
$passwordError = '';

// Hae käyttäjän nykyiset tiedot
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Käsittele salasanan vaihto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $passwordError = "Virheellinen lomake. Yritä uudelleen.";
    } elseif ($user['email'] === 'admin@demo.com') {
        $passwordError = "⚠️ Demo-tunnuksilla ei voi vaihtaa salasanaa.";
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validointi
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $passwordError = "Täytä kaikki kentät.";
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = "Uudet salasanat eivät täsmää.";
        } elseif (strlen($newPassword) < 8) {
            $passwordError = "Uuden salasanan tulee olla vähintään 8 merkkiä.";
        } else {
            // Tarkista nykyinen salasana
            if (password_verify($currentPassword, $user['password'])) {
                // Päivitä salasana
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET password = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                   $passwordSuccess = "Salasana vaihdettu onnistuneesti.";
                    
                } catch (Exception $e) {
                    $passwordError = "Salasanan vaihto epäonnistui. Yritä uudelleen.";
                }
            } else {
                $passwordError = "Nykyinen salasana on väärin.";
            }
        }
    }
}

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
                $success = "Tiedot päivitetty onnistuneesti.";
                
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
            WHERE id = ? AND user_id = ?
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
                
               $success = "Varaus peruutettu onnistuneesti.";
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
    
    // Lisää varauksen kesto lopetusaikaan
    $bookingEndTime = clone $bookingDateTime;
    $bookingEndTime->modify("+{$booking['duration']} minutes");
    
    // Jos varaus ei ole vielä loppunut JA ei ole peruutettu -> Tuleva
    if ($bookingEndTime > $now && $booking['status'] !== 'cancelled') {
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
                <button class="tab-button" onclick="openTab(event, 'password')">
                    🔑 Vaihda salasana
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
                        <p>Sinulla ei ole tulevia varauksia.</p>
                        <a href="booking.php" class="btn-primary">Varaa aika nyt</a>
                    </div>
                <?php else: ?>
                    <div class="bookings-grid">
                        <?php foreach($upcomingBookings as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-service">
                                    <?= htmlspecialchars($booking['service']) ?>
                                </div>
                                
                                <div class="booking-badge">VAHVISTETTU</div>
                                
                                <div class="booking-info">
                                    <div class="info-row">
                                        <span class="info-label">Päivä:</span>
                                        <span class="info-value"><?= date('d.m.Y', strtotime($booking['date'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Aika:</span>
                                        <span class="info-value"><?= date('H:i', strtotime($booking['time'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Kesto:</span>
                                        <span class="info-value"><?= $booking['duration'] ?> min</span>
                                    </div>
                                    <?php if ($booking['notes']): ?>
                                        <div class="info-row">
                                            <span class="info-label">Lisätiedot:</span>
                                            <span class="info-value"><?= htmlspecialchars($booking['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <form method="POST" class="booking-cancel-form">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit" 
                                            name="cancel_booking" 
                                            class="btn-cancel-booking"
                                            onclick="return confirm('Haluatko varmasti peruuttaa tämän varauksen?')">
                                        Peruuta varaus
                                    </button>
                                </form>
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
                        <p>Ei aiempia varauksia.</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-grid">
                        <?php foreach($pastBookings as $booking): ?>
                            <div class="booking-item <?= $booking['status'] === 'cancelled' ? 'cancelled' : '' ?>">
                                <div class="booking-service">
                                    <?= htmlspecialchars($booking['service']) ?>
                                </div>
                                
                                <div class="booking-badge status-<?= $booking['status'] ?>">
                                    <?php
                                    $statuses = [
                                        'pending' => 'VAHVISTETTU',
                                        'confirmed' => 'VAHVISTETTU',
                                        'cancelled' => 'PERUUTETTU',
                                        'completed' => 'SUORITETTU'
                                    ];
                                    echo $statuses[$booking['status']] ?? strtoupper($booking['status']);
                                    ?>
                                </div>
                                
                                <div class="booking-info">
                                    <div class="info-row">
                                        <span class="info-label">Päivä:</span>
                                        <span class="info-value"><?= date('d.m.Y', strtotime($booking['date'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Aika:</span>
                                        <span class="info-value"><?= date('H:i', strtotime($booking['time'])) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Kesto:</span>
                                        <span class="info-value"><?= $booking['duration'] ?> min</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Välilehti 4: Salasanan vaihto -->
            <div id="password" class="tab-content">
                <h2>Vaihda salasana</h2>
                
                <!-- Salasanan vaihdon viestit -->
                <?php if($passwordError): ?>
                    <div class="form-messages">
                        <div class="form-error"><?= htmlspecialchars($passwordError) ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if($passwordSuccess): ?>
                    <div class="form-messages">
                        <div class="form-success"><?= htmlspecialchars($passwordSuccess) ?></div>
                    </div>
                <?php endif; ?>
                
                <form class="form" method="POST" action="profile.php">
                    <?php csrf_field(); ?>
                    
                    <?php if ($user['email'] === 'admin@demo.com'): ?>
                        <div style="background-color: rgba(255, 152, 0, 0.2); border: 2px solid #ff9800; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="margin: 0; color: #fff;">
                                <strong style="color: #ff9800;">⚠️ Demo-tili:</strong> 
                                Salasanan vaihto on estetty demo-tunnuksilla turvallisuussyistä.
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <label for="current_password">Nykyinen salasana *</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           placeholder="Nykyinen salasanasi"
                           <?= $user['email'] === 'admin@demo.com' ? 'disabled' : 'required' ?>>
                    
                    <label for="new_password">Uusi salasana *</label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           placeholder="Vähintään 8 merkkiä"
                           minlength="8"
                           <?= $user['email'] === 'admin@demo.com' ? 'disabled' : 'required' ?>>
                    
                    <label for="confirm_password">Vahvista uusi salasana *</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Kirjoita uusi salasana uudelleen"
                           minlength="8"
                           <?= $user['email'] === 'admin@demo.com' ? 'disabled' : 'required' ?>>
                      
                    <button type="submit" 
                            name="change_password" 
                            class="btn-submit"
                            <?= $user['email'] === 'admin@demo.com' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                        Vaihda salasana
                    </button>
                </form>
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
    // Piilota viestit
    const messages = document.querySelectorAll('.form-messages');
    messages.forEach(msg => msg.style.display = 'none');
    
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

// Tarkista onko salasanan vaihto onnistunut tai epäonnistunut
window.addEventListener('DOMContentLoaded', function() {
    <?php if ($passwordSuccess || $passwordError): ?>
        // Avaa salasana-välilehti
        const tabs = document.getElementsByClassName('tab-content');
        for (let i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }
        const btns = document.getElementsByClassName('tab-button');
        for (let i = 0; i < btns.length; i++) {
            btns[i].classList.remove('active');
        }
        document.getElementById('password').classList.add('active');
        document.querySelector('[onclick*="password"]').classList.add('active');
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>