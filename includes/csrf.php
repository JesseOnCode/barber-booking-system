<?php
/**
 * CSRF (Cross-Site Request Forgery) suojaus
 * 
 * Estää pahantahtoiset lomakkeen lähetykset.
 * 
 * @package BarberShop
 */

/**
 * Luo uuden CSRF-tokenin
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
 * Käyttö: <?php csrf_field(); ?>
 */
function csrf_field() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}