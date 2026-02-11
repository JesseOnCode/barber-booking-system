<?php
/**
 * API-endpoint vapaiden ajanvarausaikojen hakemiseen
 * 
 * Palauttaa JSON-muotoisen listan vapaista ajoista annetulle
 * päivälle ja palvelulle. Tarkistaa olemassa olevat varaukset
 * ja palauttaa vain sellaiset ajat jotka eivät mene päällekkäin.
 * 
 * GET-parametrit:
 * - date: Päivämäärä muodossa Y-m-d (esim. 2026-02-15)
 * - service: Palvelun nimi (esim. "Hiustenleikkaus")
 * 
 * @package BarberShop
 * @author Jesse
 */

session_start();
require_once __DIR__ . '/../includes/config.php';

// Tarkista että tarvittavat parametrit on annettu
if(!isset($_GET['date']) || !isset($_GET['service'])){
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$service = $_GET['service'];

// Palveluiden kestot minuutteina
$serviceDurations = [
    "Hiustenleikkaus" => 30,
    "Parranleikkaus" => 15,
    "Koneajo" => 20,
    "Hiustenleikkaus + Parranleikkaus" => 45
];

$duration = $serviceDurations[$service] ?? 30;

// Työajat 09:00 - 16:30, 30 min välein
$workingHours = [
    "09:00","09:30","10:00","10:30","11:00","11:30",
    "12:00","12:30","13:00","13:30","14:00","14:30",
    "15:00","15:30","16:00","16:30"
];

// Hae päivän kaikki varaukset tietokannasta
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE date = ?");
$stmt->execute([$date]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$availableTimes = [];

// Käy läpi jokainen työaika ja tarkista onko se vapaa
foreach ($workingHours as $time) {
    // Luo DateTime-objektit tarkasteltavalle ajankohdalle
    $slotStart = new DateTime("$date $time");
    $slotEnd = clone $slotStart;
    $slotEnd->modify("+$duration minutes");

    $slotFree = true;
    
    // Tarkista päällekkäisyydet olemassa olevien varausten kanssa
    foreach ($bookings as $b) {
        $bStart = new DateTime($b['date'] . ' ' . $b['time']);
        $bEnd = clone $bStart;
        $bEnd->modify("+{$b['duration']} minutes");

        // Jos ajat menevät päällekkäin, merkitse aika varatuksi
        if ($slotStart < $bEnd && $slotEnd > $bStart) {
            $slotFree = false;
            break;
        }
    }

    // Lisää vapaat ajat listaan
    if ($slotFree) {
        $availableTimes[] = $time;
    }
}

// Palauta vapaat ajat JSON-muodossa
header('Content-Type: application/json');
echo json_encode($availableTimes);