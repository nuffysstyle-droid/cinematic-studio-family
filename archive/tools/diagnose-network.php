<?php
declare(strict_types=1);

echo "=== PHP Netzwerk-Diagnose ===\n\n";

// 1. allow_url_fopen
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? "yes" : "no") . "\n";

// 2. OpenSSL Extension
echo "openssl extension: " . (extension_loaded('openssl') ? "yes" : "no") . "\n";

// 3. cURL Extension
echo "curl extension: " . (extension_loaded('curl') ? "yes" : "no") . "\n";

// 4. SSL-Vars
echo "openssl.cafile: " . (ini_get('openssl.cafile') ?: "(not set)") . "\n";
echo "curl.cainfo: " . (ini_get('curl.cainfo') ?: "(not set)") . "\n";

// 5. Test api.kie.ai ohne SSL-Verify
$ctx = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 10, 'ignore_errors' => true]
]);
$result = @file_get_contents('https://api.kie.ai', false, $ctx);
echo "api.kie.ai (no verify): " . ($result === false ? "fail" : "ok") . "\n";
if ($result === false && ($e = error_get_last())) {
    echo "  error: " . $e['message'] . "\n";
}

// 6. Test api.kie.ai MIT SSL-Verify
$ctx2 = stream_context_create([
    'http' => ['timeout' => 10, 'ignore_errors' => true]
]);
$result2 = @file_get_contents('https://api.kie.ai', false, $ctx2);
echo "api.kie.ai (verify): " . ($result2 === false ? "fail" : "ok") . "\n";
if ($result2 === false && ($e = error_get_last())) {
    echo "  error: " . $e['message'] . "\n";
}

// 7. Test google.com MIT SSL-Verify
$result3 = @file_get_contents('https://www.google.com', false, $ctx2);
echo "google.com (verify): " . ($result3 === false ? "fail" : "ok") . "\n";
if ($result3 === false && ($e = error_get_last())) {
    echo "  error: " . $e['message'] . "\n";
}

echo "\n=== Diagnose Ende ===\n";
