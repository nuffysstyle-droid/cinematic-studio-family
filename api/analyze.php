<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => "ok",
    "message" => "Analyze API ist erreichbar",
    "method" => $_SERVER["REQUEST_METHOD"],
    "time" => date("Y-m-d H:i:s")
], JSON_PRETTY_PRINT);