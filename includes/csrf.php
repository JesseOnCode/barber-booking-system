<?php
/**
 * CSRF (Cross-Site Request Forgery) suojaus
 *
 * Estää pahantahtoiset lomakkeen lähetykset generoimalla ja
 * validoimalla uniikin tokenin jokaiselle sessiolle.
 *
 * Käyttö lomakkeissa:
 * - Lisää lomakkeeseen: <?php csrf_field(); ?>
 * - Validoi lomakkeen käsittelyssä: validateCSRFToken($_POST['csrf_token'])
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

/**
 * Luo uuden CSRF-tokenin
 *
 * Generoi satunnaisen 64-merkkisen hex-tokenin.
 * Token tallennetaan sessioon ja palautetaan.
 *
 * @return string CSRF-token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Tarkistaa CSRF-tokenin oikeellisuuden
 *
 * Vertaa lomakkeesta saatua tokenia session tokeniin.
 * Käyttää hash_equals() funktiota timing attack -suojaukseen.
 *
 * @param string $token Lomakkeesta saatu token
 * @return bool True jos token on oikein, false jos ei
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Näyttää CSRF-token kentän lomakkeessa
 *
 * Tulostaa piilotetun input-kentän joka sisältää CSRF-tokenin.
 *
 * Käyttö: <?php csrf_field(); ?>
 */
function csrf_field() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}