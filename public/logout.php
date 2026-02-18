<?php
/**
 * Uloskirjautuminen
 *
 * Tuhoaa käyttäjän session ja ohjaa takaisin etusivulle.
 * Tyhjentää kaikki kirjautumistiedot turvallisesti.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

session_start();

// Tyhjennä kaikki session-muuttujat
session_unset();

// Tuhoa sessio kokonaan
session_destroy();

// Ohjaa käyttäjä etusivulle
header("Location: index.php");
exit;