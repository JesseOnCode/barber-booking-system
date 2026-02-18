<?php
/**
 * Sivuston header-komponentti
 *
 * Sisältää:
 * - HTML head -osion (meta-tagit, CSS)
 * - Navigaation (desktop ja mobiili)
 * - Logon
 * - Käyttäjän kirjautumistiedot
 * - Profiili-ikonin (desktop ja mobiili)
 * - Admin-linkin (jos käyttäjä on admin)
 *
 * Käynnistää session automaattisesti jos se ei ole vielä aktiivinen.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
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
    <title>BarberShop - Ajanvaraus Kuopiossa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Varaa aika parturiin helposti verkossa. Ammattitaitoinen palvelu Kuopiossa.">
    <meta name="keywords" content="parturi, ajanvaraus, hiustenleikkaus, Kuopio">

    <link rel="stylesheet" href="/barber-booking-system/public/assets/css/main.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <a href="/barber-booking-system/public/index.php">Barber<span>Shop</span></a>
        </div>

        <!-- Profiili-ikoni mobiilissa (näkyy vain kirjautuneille) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/barber-booking-system/public/profile.php" class="mobile-profile-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
        <?php endif; ?>

        <!-- Mobiilinavigaation toggle (hamburger-valikko) -->
        <div class="nav-toggle">
             <span></span>
             <span></span>
             <span></span>
        </div>

        <!-- Päänavigaatio -->
        <nav class="main-nav">
            <ul>
                <li><a href="/barber-booking-system/public/index.php#about">Meistä</a></li>
                <li><a href="/barber-booking-system/public/index.php#services">Hinnasto</a></li>
                <li><a href="/barber-booking-system/public/index.php#booking-cta">Ajanvaraus</a></li>
                <li><a href="/barber-booking-system/public/index.php#contact">Yhteystiedot</a></li>
                
                <!-- Käyttäjän linkit mobiilinavigaatiossa -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Kirjautuneen käyttäjän nimi -->
                    <li class="mobile-only mobile-user-greeting">
                        <span>Kirjautunut: <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </li>
                    
                    <!-- Profiili-linkki ikonilla -->
                    <li class="mobile-only">
                        <a href="/barber-booking-system/public/profile.php" class="mobile-profile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Profiili
                        </a>
                    </li>
                    
                    <!-- Admin-linkki (vain jos käyttäjä on admin) -->
                    <?php if (!empty($_SESSION['is_admin'])): ?>
                        <li class="mobile-only"><a href="/barber-booking-system/public/admin/index.php">Admin-paneeli</a></li>
                    <?php endif; ?>
                    
                    <!-- Uloskirjautumislinkki -->
                    <li class="mobile-only"><a href="/barber-booking-system/public/logout.php">Kirjaudu ulos</a></li>
                <?php else: ?>
                    <!-- Kirjautumislinkki kirjautumattomille -->
                    <li class="mobile-only"><a href="/barber-booking-system/public/login.php">Kirjaudu</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Käyttäjän kirjautumistiedot (desktop) -->
        <div class="auth desktop-only">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Tervehdys käyttäjälle -->
                <span class="user-greeting">Hei, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
                
                <!-- Admin-nappi (vain admineille) -->
                <?php if (!empty($_SESSION['is_admin'])): ?>
                    <a href="/barber-booking-system/public/admin/index.php" class="btn-auth btn-admin">Admin</a>
                <?php endif; ?>
                
                <!-- Profiili-ikoni -->
                <a href="/barber-booking-system/public/profile.php" class="profile-icon" title="Oma profiili">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
                
                <!-- Uloskirjautumisnappi -->
                <a href="/barber-booking-system/public/logout.php" class="btn-auth">Kirjaudu ulos</a>
            <?php else: ?>
                <!-- Kirjautumisnappi kirjautumattomille -->
                <a href="/barber-booking-system/public/login.php" class="btn-auth">Kirjaudu / Rekisteröidy</a>
            <?php endif; ?>
        </div>

    </div>
</header>