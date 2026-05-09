<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once __DIR__ . '/service/PositionService.php';
    showPositions();
}

function showPositions() {
    $cs = new PositionService();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode(array("positions" => $cs->getAll()));
}