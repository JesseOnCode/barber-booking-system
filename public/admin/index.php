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

// Odottavat varaukset
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending' AND date >= CURDATE()");
$stats['pending'] = $stmt->fetchColumn();

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
    <section class="admin-section">
        <div class="admin-container">
            <h1>📊 Admin-paneeli</h1>
            <p class="admin-welcome">Tervetuloa, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
            
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
                
                <div class="stat-card pending">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-info">
                        <h3>Odottaa</h3>
                        <p class="stat-number"><?= $stats['pending'] ?></p>
                        <p class="stat-label">varausta</p>
                    </div>
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
                                    <th>Tila</th>
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
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?php
                                            $statuses = [
                                                'pending' => 'Odottaa',
                                                'confirmed' => 'Vahvistettu',
                                                'cancelled' => 'Peruutettu',
                                                'completed' => 'Suoritettu'
                                            ];
                                            echo $statuses[$booking['status']] ?? $booking['status'];
                                            ?>
                                        </span>
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
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>