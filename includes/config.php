<?php
// Aseta aikavyöhyke Suomeen
date_default_timezone_set('Europe/Helsinki');

/**
 * Tietokantayhteyden konfiguraatio
 *
 * Lukee asetukset .env-tiedostosta tai käyttää oletusarvoja.
 * Käsittelee myös session-turvallisuusasetukset.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

/**
 * Lataa .env-tiedosto
 *
 * Lukee .env-tiedoston rivit ja asettaa ne ympäristömuuttujiksi.
 * Ohittaa kommentit (#) ja tyhjät rivit.
 *
 * @param string $path Polku .env-tiedostoon
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ohita kommentit
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Jaa avain ja arvo
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Aseta ympäristömuuttuja
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Lataa .env projektin juuresta
loadEnv(__DIR__ . '/../.env');

/**
 * Hakee ympäristömuuttujan arvon
 *
 * Tukee boolean- ja null-arvojen muuntamista.
 *
 * @param string $key     Muuttujan avain
 * @param mixed  $default Oletusarvo jos muuttujaa ei löydy
 * @return mixed Muuttujan arvo tai oletusarvo
 */
function env($key, $default = null) {
    if (array_key_exists($key, $_ENV)) {
        $value = $_ENV[$key];

        // Muunna boolean-arvot
        if ($value === 'true' || $value === 'TRUE') {
            return true;
        }
        if ($value === 'false' || $value === 'FALSE') {
            return false;
        }
        if ($value === 'null' || $value === 'NULL') {
            return null;
        }

        return $value;
    }

    return $default;
}

/**
 * Session-turvallisuusasetukset
 * Konfiguroidaan ennen kuin sessio käynnistyy
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
}

// Tietokanta-asetukset (.env-tiedostosta tai oletusarvot)
$host = env('DB_HOST', 'localhost');
$db   = env('DB_NAME', 'barbershop');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');

// Sovelluksen asetukset
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', false));
define('APP_URL', env('APP_URL', 'http://localhost'));

/**
 * Luo tietokantayhteys PDO:lla
 * Käyttää UTF-8 merkistöä ja prepared statements -suojausta
 */
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Tuotannossa piilotetaan virheviestit, kehityksessä näytetään
    if (APP_ENV === 'production') {
        die("Tietokantayhteys epäonnistui. Yritä hetken kuluttua uudelleen.");
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}