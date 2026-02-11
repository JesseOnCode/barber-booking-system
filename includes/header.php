<?php
/**
 * Sivuston header-komponentti
 * 
 * Sisältää navigaation, logon ja käyttäjän kirjautumistiedot.
 * Käynnistää session jos se ei ole jo aktiivinen.
 * 
 * @package BarberShop
 * @author Jesse
 */

// Käynnistä sessio jos ei ole vielä käynnissä
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
    <meta name="description" content="Varaa aika parturiin helposti verkossa. Ammattitaitoinen palvelu Kuopiossa.">
    <meta name="keywords" content="parturi, ajanvaraus, hiustenleikkaus, Kuopio">

    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <a href="index.php">Barber<span>Shop</span></a>
        </div>

        <!-- Mobiilinavigaation toggle -->
        <div class="nav-toggle">
             <span></span>
             <span></span>
             <span></span>
        </div>

        <!-- Päänavigaatio -->
        <nav class="main-nav">
            <ul>
                <li><a href="index.php#hero">Etusivu</a></li>
                <li><a href="index.php#about">Meistä</a></li>
                <li><a href="index.php#services">Palvelut</a></li>
                <li><a href="index.php#booking-cta">Ajanvaraus</a></li>
                <li><a href="index.php#contact">Yhteystiedot</a></li>
                
               <!-- Käyttäjän linkit mobiilissa -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="mobile-only mobile-user-greeting">
                        <span>Kirjautunut: <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </li>
                    <li class="mobile-only"><a href="profile.php">👤 Profiili</a></li>
                    <li class="mobile-only"><a href="logout.php">🚪 Kirjaudu ulos</a></li>
                <?php else: ?>
                    <li class="mobile-only"><a href="login.php">🔐 Kirjaudu</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Käyttäjän kirjautumistiedot (desktop) -->
        <div class="auth desktop-only">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-greeting">Hei, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="profile.php" class="btn-auth btn-profile">Profiili</a>
                <a href="logout.php" class="btn-auth">Kirjaudu ulos</a>
            <?php else: ?>
                <a href="login.php" class="btn-auth">Kirjaudu / Rekisteröidy</a>
            <?php endif; ?>
        </div>

    </div>
</header>