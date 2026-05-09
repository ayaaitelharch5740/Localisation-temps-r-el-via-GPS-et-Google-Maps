<?php
header('Content-Type: application/json; charset=utf-8');

if($_SERVER["REQUEST_METHOD"] != "POST"){
    http_response_code(405);
    echo json_encode(["ok" => false, "error" => "POST required"]);
    exit;
}

include_once __DIR__ . '/service/PositionService.php';
include_once __DIR__ . '/classe/Position.php';

// Lecture POST
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$date = $_POST['date'] ?? null;
$imei = $_POST['imei'] ?? null;

// IP vue côté serveur (objectif TP)
$ip = $_SERVER['REMOTE_ADDR']; // :contentReference[oaicite:8]{index=8}

if ($latitude === null || $longitude === null || $date === null || $imei === null) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Missing params", "ip" => $ip]);
    exit;
}

try {
    $ss = new PositionService();
    $ss->create(new Position(null, $latitude, $longitude, $date, $imei));
    echo json_encode(["ok" => true, "ip" => $ip]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => $e->getMessage(), "ip" => $ip]);
}