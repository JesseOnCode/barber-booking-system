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
            $success = "✅ Varaus poistettu onnistuneesti.";
        } catch (Exception $e) {
            $error = "Varauksen poisto epäonnistui.";
        }
    }
}

// Käsittele uuden varauksen lisäys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Virheellinen lomake.";
    } else {
        $userId = (int)$_POST['user_id'];
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
        
        if (empty($userId) || empty($service) || empty($date) || empty($time)) {
            $error = "Täytä kaikki pakolliset kentät.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, service, date, time, duration, notes, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
                ");
                $stmt->execute([$userId, $service, $date, $time, $duration, $notes]);
                $success = "✅ Uusi varaus lisätty onnistuneesti!";
            } catch (Exception $e) {
                $error = "Varauksen lisäys epäonnistui: " . $e->getMessage();
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

// Hae viimeisimmät varaukset
$stmt = $pdo->query("
    SELECT b.*, u.first_name, u.last_name, u.email 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.date >= CURDATE()
    ORDER BY b.date ASC, b.time ASC 
    LIMIT 20
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/header.php';
?>

<main>
    <div class="admin-section">
        <div class="admin-container">
            <h1>📊 Admin-paneeli</h1>
            <p class="admin-welcome">Tervetuloa, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            
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
            
            <!-- Tilastokortit -->
            <div class="stats-grid">
                <div class="stat-card today">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3>Tänään</h3>
                        <p class="stat-number"><?= $stats['today'] ?></p>
                        <p class="stat-label">varausta</p>
                    </div>
                </div>
                
                <div class="stat-card week">
                    <div class="stat-icon">📆</div>
                    <div class="stat-info">
                        <h3>Tällä viikolla</h3>
                        <p class="stat-number"><?= $stats['week'] ?></p>
                        <p class="stat-label">varausta</p>
                    </div>
                </div>
                
                <div class="stat-card month">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3>Tässä kuussa</h3>
                        <p class="stat-number"><?= $stats['month'] ?></p>
                        <p class="stat-label">varausta</p>
                    </div>
                </div>
            </div>
            
            <!-- Lisää uusi varaus -->
            <div class="admin-add-booking">
                <button class="btn-toggle" onclick="toggleAddForm()">➕ Lisää uusi varaus</button>
                
                <div id="addBookingForm" class="add-form" style="display: none;">
                    <h3>Uusi varaus</h3>
                    
                    <form method="POST" class="admin-form">
                        <?php csrf_field(); ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_id">Asiakas *</label>
                                <select name="user_id" id="user_id" required>
                                    <option value="">-- Valitse asiakas --</option>
                                    <?php
                                    $users = $pdo->query("SELECT id, first_name, last_name, email FROM users ORDER BY first_name")->fetchAll();
                                    foreach($users as $u):
                                    ?>
                                        <option value="<?= $u['id'] ?>">
                                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> 
                                            (<?= htmlspecialchars($u['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
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
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date">Päivämäärä *</label>
                                <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="time">Aika *</label>
                                <input type="time" name="time" id="time" value="09:00" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Lisätiedot</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Valinnainen..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="add_booking" class="btn-submit">💾 Tallenna varaus</button>
                            <button type="button" class="btn-cancel" onclick="toggleAddForm()">❌ Peruuta</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tulevat varaukset -->
            <div class="admin-bookings">
                <h2>Tulevat varaukset</h2>
                
                <?php if (empty($bookings)): ?>
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
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bookings as $booking): ?>
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
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Haluatko varmasti poistaa tämän varauksen?')">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" name="delete_booking" class="btn-delete">
                                                🗑️ Poista
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
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>