<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Barber Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="logo">
            <a href="index.php">Barber<span>Shop</span></a>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php#hero">Etusivu</a></li>
                <li><a href="index.php#about">Meistä</a></li>
                <li><a href="index.php#services">Palvelut</a></li>
                <li><a href="index.php#booking-cta">Ajanvaraus</a></li>
                <li><a href="index.php#contact">Yhteystiedot</a></li>
            </ul>
        </nav>

        <div class="nav-toggle">
             <span></span>
             <span></span>
             <span></span>
        </div>

        <div class="auth">
    <?php if(isset($_SESSION['user_id'])): ?>
        <span class="user-greeting">Hei, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="logout.php" class="btn-auth">Kirjaudu ulos</a>
    <?php else: ?>
        <a href="login.php" class="btn-auth">Kirjaudu / Rekisteröidy</a>
    <?php endif; ?>
</div>

    </div>
</header>
