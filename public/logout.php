<?php
/**
 * Käyttäjän uloskirjautuminen
 * 
 * Tuhoaa käyttäjän session ja ohjaa takaisin etusivulle.
 * 
 * @package BarberShop
 * @author Jesse
 */

session_start();

// Tyhjennä kaikki session-muuttujat
session_unset();

// Tuhoa sessio kokonaan
session_destroy();

// Ohjaa käyttäjä etusivulle
header("Location: index.php");
exit;