<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if(!isset($_GET['date']) || !isset($_GET['service'])){
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$service = $_GET['service'];

$serviceDurations = [
    "Hiustenleikkaus" => 30,
    "Parranleikkaus" => 15,
    "Koneajo" => 20,
    "Hiustenleikkaus + Parranleikkaus" => 45
];

$duration = $serviceDurations[$service];

// Työajat 09:00 - 16:30, 30 min välein
$workingHours = ["09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30",
                 "13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30"];

// Hae päivän varaukset
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE date = ?");
$stmt->execute([$date]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$availableTimes = [];

foreach ($workingHours as $time) {
    $slotStart = new DateTime("$date $time");
    $slotEnd = clone $slotStart;
    $slotEnd->modify("+$duration minutes");

    $slotFree = true;
    foreach ($bookings as $b) {
        $bStart = new DateTime($b['date'] . ' ' . $b['time']);
        $bEnd = clone $bStart;
        $bEnd->modify("+{$b['duration']} minutes");

        if ($slotStart < $bEnd && $slotEnd > $bStart) {
            $slotFree = false;
            break;
        }
    }

    if ($slotFree) {
        $availableTimes[] = $time;
    }
}

echo json_encode($availableTimes);
