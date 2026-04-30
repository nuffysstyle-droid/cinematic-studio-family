<?php

$url = "https://cinematic-studio-family.onrender.com/api/process.php";

$response = file_get_contents($url);

echo "<pre>";
echo $response;
echo "</pre>";