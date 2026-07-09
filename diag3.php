<?php
// Diagnostic 3: Full authenticated route test through the actual web server
// This script logs in via curl and tests every route

ini_set('display_errors', '1');
error_reporting(E_ALL);

$baseUrl = 'http://localhost:8765';
$cookieFile = __DIR__ . '/test_cookie.txt';

// Step 1: Get login page to extract CSRF token
echo "=== Step 1: Fetch login page for CSRF token ===\n";
$loginPage = curlGet($baseUrl . '/login');
preg_match('/name="csrf_token" value="([^"]+)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? '';
echo "CSRF token: " . substr($csrfToken, 0, 20) . "...\n";

// Step 2: Login
echo "\n=== Step 2: Login as admin ===\n";
$loginResult = curlPost($baseUrl . '/login', [
    'csrf_token' => $csrfToken,
    'username' => 'admin',
    'password' => 'password'
], $cookieFile);
echo "Login response code: " . $loginResult['code'] . "\n";
echo "Login location: " . ($loginResult['headers']['location'] ?? 'none') . "\n";

// Step 3: Test all routes
echo "\n=== Step 3: Test all authenticated routes ===\n";
$routes = [
    '/dashboard',
    '/buyers',
    '/products',
    '/quotations',
    '/proforma-invoices',
    '/commercial-invoices',
    '/packing-lists',
    '/shipping-bills',
    '/bill-of-ladings',
    '/certificate-of-origins',
    '/export-documents',
    '/company',
    '/settings',
    '/users',
    '/roles',
    '/permissions',
    '/reports',
];

foreach ($routes as $route) {
    $result = curlGet($baseUrl . $route, $cookieFile);
    $bodyLength = strlen($result['body']);
    $hasContent = strpos($result['body'], '<main class="content">') !== false;
    $hasPageHeader = strpos($result['body'], 'page-header') !== false;
    $hasDoctype = strpos($result['body'], '<!DOCTYPE html>') !== false;
    $isBlank = $bodyLength < 100 && !$hasDoctype;
    
    $status = 'OK';
    if ($isBlank) $status = 'BLANK!';
    elseif (!$hasContent) $status = 'NO CONTENT AREA';
    elseif (!$hasPageHeader) $status = 'NO PAGE HEADER';
    
    printf("%-30s HTTP %d  body=%6d  %s\n", $route, $result['code'], $bodyLength, $status);
    
    // If blank, show the body
    if ($isBlank || $result['code'] >= 400) {
        echo "  BODY: " . substr($result['body'], 0, 500) . "\n";
    }
}

// Helper functions
function curlGet($url, $cookieFile = null) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    }
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = parseHeaders(substr($response, 0, $headerSize));
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
}

function curlPost($url, $data, $cookieFile = null) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ]);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    }
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = parseHeaders(substr($response, 0, $headerSize));
    $body = substr($response, $headerSize);
    curl_close($ch);
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
}

function parseHeaders($headerStr) {
    $headers = [];
    $lines = explode("\r\n", $headerStr);
    foreach ($lines as $line) {
        if (strpos($line, ': ') !== false) {
            [$key, $val] = explode(': ', $line, 2);
            $headers[strtolower($key)] = $val;
        }
    }
    return $headers;
}

echo "\n=== DONE ===\n";