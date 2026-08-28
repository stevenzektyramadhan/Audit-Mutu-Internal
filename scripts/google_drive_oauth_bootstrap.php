<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This bootstrap must be run from CLI.\n");
    exit(1);
}

$root = dirname(__DIR__);
define('ENVIRONMENT', getenv('CI_ENV') !== FALSE ? getenv('CI_ENV') : 'development');
if (ENVIRONMENT === 'production') {
    fwrite(STDERR, "This bootstrap is development-only.\n");
    exit(1);
}

$autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Composer dependencies are unavailable.\n");
    exit(1);
}
require_once $autoload;

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php scripts/google_drive_oauth_bootstrap.php /abs/oauth-client.json /abs/refresh-token.json\n");
    exit(1);
}

$client_path = $argv[1];
$token_path = $argv[2];
$web_root = realpath($root);
if (!is_string($client_path) || !$client_path || !($client_path[0] === '/' || preg_match('/\A[A-Za-z]:[\\\\\/]/', $client_path))) {
    fwrite(STDERR, "Credential paths must be absolute.\n");
    exit(1);
}
$client_parent = realpath(dirname($client_path));
if ($client_parent === FALSE || !is_dir($client_parent) || ($web_root !== FALSE && strpos($client_parent . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) === 0)) {
    fwrite(STDERR, "OAuth client JSON must be outside the web root.\n");
    exit(1);
}

if (!is_string($token_path) || !$token_path || !($token_path[0] === '/' || preg_match('/\A[A-Za-z]:[\\\\\/]/', $token_path))) {
    fwrite(STDERR, "Credential paths must be absolute.\n");
    exit(1);
}
$token_parent = realpath(dirname($token_path));
if ($token_parent === FALSE || !is_dir($token_parent) || !is_writable($token_parent) || ($web_root !== FALSE && strpos($token_parent . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) === 0)) {
    fwrite(STDERR, "Refresh token path parent must be writable outside the web root.\n");
    exit(1);
}

if (file_exists($token_path)) {
    fwrite(STDERR, "Refresh token file already exists.\n");
    exit(1);
}

if (!is_file($client_path) || !is_readable($client_path)) {
    fwrite(STDERR, "OAuth client JSON is not readable.\n");
    exit(1);
}
$client_realpath = realpath($client_path);
if ($client_realpath === FALSE || ($web_root !== FALSE && strpos($client_realpath . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) === 0)) {
    fwrite(STDERR, "OAuth client JSON must be outside the web root.\n");
    exit(1);
}
$client_json = file_get_contents($client_path);
$client_data = $client_json === FALSE ? NULL : json_decode($client_json, TRUE);
if (!is_array($client_data) || !isset($client_data['installed']) || !is_array($client_data['installed'])) {
    fwrite(STDERR, "OAuth client JSON must be a Google desktop client secret.\n");
    exit(1);
}

$port = 0;
$server = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (!$server) {
    fwrite(STDERR, "Unable to bind loopback callback.\n");
    exit(1);
}
$name = stream_socket_get_name($server, FALSE);
if (!is_string($name) || !preg_match('/:(\d+)\z/', $name, $matches)) {
    fclose($server);
    fwrite(STDERR, "Unable to determine callback port.\n");
    exit(1);
}
$port = (int) $matches[1];
$redirect_uri = 'http://127.0.0.1:' . $port . '/oauth2callback';
$state = bin2hex(random_bytes(24));

$client = new Google_Client();
$client->setApplicationName('AMI Evidence Storage OAuth Bootstrap');
$client->setAuthConfig($client_path);
$client->setScopes([Google_Service_Drive::DRIVE]);
$client->setRedirectUri($redirect_uri);
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->setState($state);

fwrite(STDOUT, "The localhost callback listener will wait up to 15 minutes.\n");
fwrite(STDOUT, "Open this URL, approve access, then return here after the localhost callback completes:\n");
fwrite(STDOUT, $client->createAuthUrl() . "\n");

$connection = @stream_socket_accept($server, 900);
fclose($server);
if (!$connection) {
    fwrite(STDERR, "Timed out waiting up to 15 minutes for the OAuth callback.\n");
    exit(1);
}

$request_line = fgets($connection, 4096);
while (($line = fgets($connection, 4096)) !== FALSE && trim($line) !== '') {
}
$query = parse_url(is_string($request_line) ? explode(' ', $request_line)[1] : '', PHP_URL_QUERY);
parse_str(is_string($query) ? $query : '', $params);
$ok = isset($params['state'], $params['code']) && hash_equals($state, (string) $params['state']) && is_string($params['code']) && $params['code'] !== '';
$body = $ok ? 'OAuth consent received. You may close this tab.' : 'OAuth consent failed.';
fwrite($connection, "HTTP/1.1 " . ($ok ? '200 OK' : '400 Bad Request') . "\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
fclose($connection);
if (!$ok) {
    fwrite(STDERR, "OAuth callback validation failed.\n");
    exit(1);
}

$token = $client->fetchAccessTokenWithAuthCode((string) $params['code']);
if (!is_array($token) || isset($token['error']) || !isset($token['refresh_token']) || trim((string) $token['refresh_token']) === '') {
    fwrite(STDERR, "OAuth did not return a refresh token. Revoke prior consent and retry.\n");
    exit(1);
}

$temporary_path = $token_path . '.tmp.' . bin2hex(random_bytes(8));
$payload = json_encode(['refresh_token' => (string) $token['refresh_token']], JSON_UNESCAPED_SLASHES) . "\n";
if (file_put_contents($temporary_path, $payload, LOCK_EX) === FALSE || !chmod($temporary_path, 0600) || !rename($temporary_path, $token_path) || !chmod($token_path, 0600)) {
    if (is_file($temporary_path)) {
        unlink($temporary_path);
    }
    fwrite(STDERR, "Unable to write refresh token file.\n");
    exit(1);
}

fwrite(STDOUT, "Refresh token file written.\n");
