<?php
/**
 * Admin-paneelin etusivu
 * 
 * Näyttää tilastoja ja viimeisimmät varaukset.
 * Vain adminit voivat päästä tänne.
 * 
 * @package BarberShop
 * @author Jesse
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/csrf.php';

$success = '';
$error = '';

// Tarkista kirjautuminen
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Tarkista admin-oikeudet
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    // Ei admin-oikeuksia, ohjaa takaisin etusivulle
    header("Location: ../index.php");
    exit;
}

// Käsittele varauksen poisto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake.";
    } else {
        $bookingId = (int)$_POST['booking_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $success = "Varaus poistettu onnistuneesti.";
        } catch (Exception $e) {
            $error = "Varauksen poisto epäonnistui.";
        }
    }
}

// Käsittele asiakkaan tietojen poisto (GDPR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_data'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake.";
    } else {
        $userId = (int)$_POST['user_id'];
        
        try {
            // Poista ensin kaikki käyttäjän varaukset
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Poista käyttäjä
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            $success = "Asiakkaan kaikki tiedot poistettu onnistuneesti.";
        } catch (Exception $e) {
            $error = "Tietojen poisto epäonnistui: " . $e->getMessage();
        }
    }
}

// Käsittele uuden varauksen lisäys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake.";
    } else {
        $isNewCustomer = isset($_POST['new_customer']) && $_POST['new_customer'] === '1';
        $service = $_POST['service'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $notes = trim($_POST['notes'] ?? '');
        
        $serviceDurations = [
            "Hiustenleikkaus" => 30,
            "Parranleikkaus" => 15,
            "Koneajo" => 20,
            "Hiustenleikkaus + Parranleikkaus" => 45
        ];
        
        $duration = $serviceDurations[$service] ?? 30;
        
        if ($isNewCustomer) {
            // Uusi asiakas - tarkista ENSIN aika, sitten luo käyttäjä
            $firstName = trim($_POST['new_first_name'] ?? '');
            $lastName = trim($_POST['new_last_name'] ?? '');
            $email = trim($_POST['new_email'] ?? '');
            
            if (empty($firstName) || empty($lastName) || empty($email)) {
                $error = "Etunimi, sukunimi ja sähköposti ovat pakollisia uudelle asiakkaalle.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Virheellinen sähköpostiosoite.";
            } elseif (empty($service) || empty($date) || empty($time)) {
                $error = "Täytä kaikki pakolliset kentät.";
            } elseif (new DateTime("$date $time") <= new DateTime()) {
                $error = "Et voi varata aikaa menneisyyteen.";
            } else {
                // Tarkista ettei sähköposti ole jo käytössä
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "Tämä sähköposti on jo rekisteröity. Valitse 'Valitse olemassa oleva asiakas'.";
                } else {
                    // Tarkista päällekkäisyydet ENNEN asiakkaan luontia
                    $bookingStart = new DateTime("$date $time");
                    $bookingEnd = clone $bookingStart;
                    $bookingEnd->modify("+{$duration} minutes");
                    
                    // Hae kaikki varaukset samalle päivälle
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE date = ? AND status != 'cancelled'");
                    $stmt->execute([$date]);
                    $existingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $hasConflict = false;
                    foreach ($existingBookings as $existing) {
                        $existingStart = new DateTime($existing['date'] . ' ' . $existing['time']);
                        $existingEnd = clone $existingStart;
                        $existingEnd->modify("+{$existing['duration']} minutes");
                        
                        if ($bookingStart < $existingEnd && $bookingEnd > $existingStart) {
                            $hasConflict = true;
                            break;
                        }
                    }
                    
                    if ($hasConflict) {
                        $error = "Valittu aika on jo varattu. Valitse toinen aika.";
                    } else {
                        // Aika on vapaa - nyt voidaan luoda asiakas JA varaus
                        try {
                            // Luo satunnainen salasana (asiakas voi vaihtaa sen myöhemmin)
                            $randomPassword = bin2hex(random_bytes(16));
                            $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
                            
                            // Luo käyttäjä
                            $stmt = $pdo->prepare("
                                INSERT INTO users (first_name, last_name, email, password)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
                            $userId = $pdo->lastInsertId();
                            
                            // Luo varaus
                            $stmt = $pdo->prepare("
                                INSERT INTO bookings (user_id, service, date, time, duration, notes, status)
                                VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
                            ");
                            $stmt->execute([$userId, $service, $date, $time, $duration, $notes]);
                            
                            $success = "Uusi asiakas ja varaus lisätty onnistuneesti.";
                        } catch (Exception $e) {
                            $error = "Asiakkaan tai varauksen lisäys epäonnistui: " . $e->getMessage();
                        }
                    }
                }
            }
        } else {
            // Olemassa oleva asiakas
            $userId = (int)$_POST['user_id'];
            
            if (empty($userId) || empty($service) || empty($date) || empty($time)) {
                $error = "Täytä kaikki pakolliset kentät.";
            } elseif (new DateTime("$date $time") <= new DateTime()) {
                $error = "Et voi varata aikaa menneisyyteen.";
            } else {
                // Tarkista päällekkäisyydet
                $bookingStart = new DateTime("$date $time");
                $bookingEnd = clone $bookingStart;
                $bookingEnd->modify("+{$duration} minutes");
                
                // Hae kaikki varaukset samalle päivälle
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE date = ? AND status != 'cancelled'");
                $stmt->execute([$date]);
                $existingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $hasConflict = false;
                foreach ($existingBookings as $existing) {
                    $existingStart = new DateTime($existing['date'] . ' ' . $existing['time']);
                    $existingEnd = clone $existingStart;
                    $existingEnd->modify("+{$existing['duration']} minutes");
                    
                    // Tarkista päällekkäisyys
                    if ($bookingStart < $existingEnd && $bookingEnd > $existingStart) {
                        $hasConflict = true;
                        break;
                    }
                }
                
                if ($hasConflict) {
                    $error = "Valittu aika on jo varattu. Valitse toinen aika.";
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO bookings (user_id, service, date, time, duration, notes, status)
                            VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
                        ");
                        $stmt->execute([$userId, $service, $date, $time, $duration, $notes]);
                        $success = "Uusi varaus lisätty onnistuneesti.";
                    } catch (Exception $e) {
                        $error = "Varauksen lisäys epäonnistui: " . $e->getMessage();
                    }
                }
            }
        }
    }
}

// Hae tilastoja
$stats = [];

// Tämän päivän varaukset
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE date = CURDATE() AND status != 'cancelled'");
$stats['today'] = $stmt->fetchColumn();

// Viikon varaukset
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE WEEK(date) = WEEK(CURDATE()) AND status != 'cancelled'");
$stats['week'] = $stmt->fetchColumn();

// Kuukauden varaukset
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) AND status != 'cancelled'");
$stats['month'] = $stmt->fetchColumn();

// Hae tulevat varaukset
$stmt = $pdo->query("
    SELECT b.*, u.first_name, u.last_name, u.email 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.date >= CURDATE() AND b.status != 'cancelled'
    ORDER BY b.date ASC, b.time ASC 
    LIMIT 50
");
$upcomingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hae menneet varaukset (viimeiset 100)
$stmt = $pdo->query("
    SELECT b.*, u.first_name, u.last_name, u.email 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.date < CURDATE() OR b.status = 'cancelled'
    ORDER BY b.date DESC, b.time DESC 
    LIMIT 100
");
$pastBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hae kaikki asiakkaat (ei admineja)
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(b.id) as total_bookings,
           MAX(b.date) as last_booking_date
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    WHERE u.is_admin = 0
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<main>
    <div class="admin-section">
        <div class="admin-container">
            <h1>Admin Dashboard</h1>
            <p class="admin-welcome">Tervetuloa, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            
            <!-- Viestit -->
            <?php if($error): ?>
                <div class="admin-message error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="admin-message success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <!-- Lisää uusi varaus -->
            <div class="admin-add-booking">
                <button class="btn-toggle" onclick="toggleAddForm()">+ Lisää uusi varaus</button>
                
                <div id="addBookingForm" class="add-form" style="display: none;">
                    <h3>Uusi varaus</h3>
                    
                    <form method="POST" class="admin-form">
                        <?php csrf_field(); ?>
                        
                        <!-- Valinta: Uusi vai olemassa oleva asiakas -->
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 10px;">Asiakastyyppi *</label>
                        <div style="margin-bottom: 8px;">
                            <input type="radio" name="customer_type" value="existing" id="existing" checked>
                            <label for="existing">Valitse olemassa oleva asiakas</label>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <input type="radio" name="customer_type" value="new" id="new">
                            <label for="new">Uusi asiakas (puhelinvaraus)</label>
                        </div>
                        
                        <!-- Olemassa oleva asiakas -->
                        <div id="existingCustomer" class="customer-section">
                            <div class="form-group">
                                <label for="user_id">Valitse asiakas *</label>
                                <select name="user_id" id="user_id">
                                    <option value="">-- Valitse asiakas --</option>
                                    <?php
                                    $users = $pdo->query("SELECT id, first_name, last_name, email FROM users WHERE is_admin = 0 ORDER BY first_name")->fetchAll();
                                    foreach($users as $u):
                                    ?>
                                        <option value="<?= $u['id'] ?>">
                                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> 
                                            (<?= htmlspecialchars($u['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Uusi asiakas -->
                        <div id="newCustomer" class="customer-section" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_first_name">Etunimi *</label>
                                    <input type="text" name="new_first_name" id="new_first_name" placeholder="Esim. Matti">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_last_name">Sukunimi *</label>
                                    <input type="text" name="new_last_name" id="new_last_name" placeholder="Esim. Meikäläinen">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_email">Sähköposti *</label>
                                <input type="email" name="new_email" id="new_email" placeholder="esim@email.fi">
                            </div>
                        </div>
                        
                        <!-- Palvelu ja aika -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="service">Palvelu *</label>
                                <select name="service" id="service" required>
                                    <option value="">-- Valitse palvelu --</option>
                                    <option value="Hiustenleikkaus">Hiustenleikkaus - 30 min</option>
                                    <option value="Parranleikkaus">Parranleikkaus - 15 min</option>
                                    <option value="Koneajo">Koneajo - 20 min</option>
                                    <option value="Hiustenleikkaus + Parranleikkaus">Hiustenleikkaus + Parranleikkaus - 45 min</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="date">Päivämäärä *</label>
                                <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="time">Aika *</label>
                                <input type="time" name="time" id="time" value="09:00" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Lisätiedot</label>
                                <textarea name="notes" id="notes" rows="3" placeholder="Valinnainen..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="add_booking" class="btn-submit">Tallenna varaus</button>
                            <button type="button" class="btn-cancel" onclick="toggleAddForm()">Peruuta</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Välilehdet -->
            <div class="admin-tabs">
                <button class="admin-tab-btn active" onclick="switchTab('upcoming')">
                    <span class="tab-text">Tulevat varaukset</span>
                    <span class="tab-badge"><?= count($upcomingBookings) ?></span>
                </button>
                <button class="admin-tab-btn" onclick="switchTab('past')">
                    <span class="tab-text">Menneet varaukset</span>
                    <span class="tab-badge"><?= count($pastBookings) ?></span>
                </button>
                <button class="admin-tab-btn" onclick="switchTab('customers')">
                    <span class="tab-text">Asiakkaat</span>
                    <span class="tab-badge"><?= count($customers) ?></span>
                </button>
                <button class="admin-tab-btn" onclick="switchTab('stats')">
                    <span class="tab-text">Tilastot</span>
                </button>
            </div>
            
            <!-- Tulevat varaukset -->
            <div id="upcoming" class="admin-tab-content active">
                <h2>Tulevat varaukset</h2>
                
                <?php if (empty($upcomingBookings)): ?>
                    <p class="no-data">Ei tulevia varauksia.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Asiakas</th>
                                    <th>Palvelu</th>
                                    <th>Päivä</th>
                                    <th>Aika</th>
                                    <th>Kesto</th>
                                    <th>Lisätiedot</th>
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($upcomingBookings as $booking): ?>
                                <tr>
                                    <td><?= $booking['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong>
                                        <br>
                                        <small><?= htmlspecialchars($booking['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['service']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($booking['date'])) ?></td>
                                    <td><?= date('H:i', strtotime($booking['time'])) ?></td>
                                    <td><?= $booking['duration'] ?> min</td>
                                    <td>
                                        <?php if (!empty($booking['notes'])): ?>
                                            <?= htmlspecialchars($booking['notes']) ?>
                                        <?php else: ?>
                                            <span style="color: #666;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Haluatko varmasti poistaa tämän varauksen?')">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" name="delete_booking" class="btn-delete">
                                                Poista
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Menneet varaukset -->
            <div id="past" class="admin-tab-content">
                <h2>Menneet varaukset</h2>
                
                <?php if (empty($pastBookings)): ?>
                    <p class="no-data">Ei menneitä varauksia.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Asiakas</th>
                                    <th>Palvelu</th>
                                    <th>Päivä</th>
                                    <th>Aika</th>
                                    <th>Tila</th>
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pastBookings as $booking): ?>
                                <tr>
                                    <td><?= $booking['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong>
                                        <br>
                                        <small><?= htmlspecialchars($booking['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['service']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($booking['date'])) ?></td>
                                    <td><?= date('H:i', strtotime($booking['time'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?php
                                            $statuses = [
                                                'confirmed' => 'Vahvistettu',
                                                'cancelled' => 'Peruutettu',
                                                'completed' => 'Suoritettu'
                                            ];
                                            echo $statuses[$booking['status']] ?? $booking['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Haluatko varmasti poistaa tämän varauksen?')">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" name="delete_booking" class="btn-delete">
                                                Poista varaus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Asiakkaat -->
            <div id="customers" class="admin-tab-content">
                <h2>Asiakkaat</h2>
                <p class="gdpr-notice">
                    <strong>GDPR-huomio:</strong> Tästä osiosta voit poistaa asiakkaan kaikki tiedot ja varaukset pysyvästi.
                </p>

                <!-- Hakukenttä -->
                <div class="search-box" style="margin-bottom: 20px;">
                    <input type="text" 
                           id="customerSearch" 
                           placeholder="Hae nimellä tai sähköpostilla..." 
                           style="width: 100%; max-width: 400px; padding: 10px; border: 1px solid #555; border-radius: 6px; background-color: #1a1a1a; color: #fff; font-size: 14px;">
                </div>
                
                <?php if (empty($customers)): ?>
                    <p class="no-data">Ei asiakkaita.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nimi</th>
                                    <th>Sähköposti</th>
                                    <th>Varauksia yhteensä</th>
                                    <th>Viimeisin varaus</th>
                                    <th>Rekisteröitynyt</th>
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers as $customer): ?>
                                <tr>
                                    <td><?= $customer['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td><?= $customer['total_bookings'] ?> kpl</td>
                                    <td>
                                        <?php if ($customer['last_booking_date']): ?>
                                            <?= date('d.m.Y', strtotime($customer['last_booking_date'])) ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Ei varauksia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($customer['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('VAROITUS: Tämä poistaa KAIKKI asiakkaan tiedot ja varaukset (<?= $customer['total_bookings'] ?> kpl) pysyvästi!\n\nAsiakas: <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>\n\nOletko varma että haluat jatkaa?')">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
                                            <button type="submit" name="delete_user_data" class="btn-gdpr">
                                                Poista asiakas
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Tilastot -->
            <div id="stats" class="admin-tab-content">
                <h2>Tilastot</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Tänään</h3>
                            <p class="stat-number"><?= $stats['today'] ?></p>
                            <p class="stat-label">varauksia</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Tällä viikolla</h3>
                            <p class="stat-number"><?= $stats['week'] ?></p>
                            <p class="stat-label">varauksia</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Tässä kuussa</h3>
                            <p class="stat-number"><?= $stats['month'] ?></p>
                            <p class="stat-label">varauksia</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="admin-back">
                <a href="/barber-booking-system/public/index.php">← Takaisin etusivulle</a>
            </p>
        </div>
    </div>
</main>

<script>
function toggleAddForm() {
    const form = document.getElementById('addBookingForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

function switchTab(tabName) {
    // Piilota viestit
    const messages = document.querySelectorAll('.admin-message');
    messages.forEach(msg => msg.style.display = 'none');
    
    // Piilota kaikki välilehdet
    const tabs = document.getElementsByClassName('admin-tab-content');
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Poista aktiivinen tila napeista
    const btns = document.getElementsByClassName('admin-tab-btn');
    for (let i = 0; i < btns.length; i++) {
        btns[i].classList.remove('active');
    }
    
    // Näytä valittu välilehti
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function toggleCustomerType() {
    const isNew = document.querySelector('input[name="new_customer"]:checked').value === '1';
    const existingSection = document.getElementById('existingCustomer');
    const newSection = document.getElementById('newCustomer');
    const userSelect = document.getElementById('user_id');
    
    if (isNew) {
        existingSection.style.display = 'none';
        newSection.style.display = 'block';
        userSelect.removeAttribute('required');
    } else {
        existingSection.style.display = 'block';
        newSection.style.display = 'none';
        userSelect.setAttribute('required', 'required');
    }
}

// Asiakashaku
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customerSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.querySelector('#customers .admin-table tbody');
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            
            for (let row of rows) {
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }
});
</script>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>