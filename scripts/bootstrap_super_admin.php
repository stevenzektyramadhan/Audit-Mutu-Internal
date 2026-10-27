<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Script ini hanya dapat dijalankan dari terminal.\n");
    exit(1);
}

function prompt(string $label, ?string $default = null, bool $hidden = false): string
{
    $suffix = $default === null ? ': ' : ' [' . $default . ']: ';
    fwrite(STDOUT, $label . $suffix);
    if ($hidden && DIRECTORY_SEPARATOR === '/') {
        shell_exec('stty -echo');
    }
    $value = fgets(STDIN);
    if ($hidden && DIRECTORY_SEPARATOR === '/') {
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    $value = $value === false ? '' : trim($value);
    return $value === '' && $default !== null ? $default : $value;
}

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

// 1. Cek parameter --config jika diberikan
$configPath = null;
if (isset($argv[1])) {
    if (strpos($argv[1], '--config=') === 0) {
        $configPath = substr($argv[1], strlen('--config='));
    } else {
        fail('Gunakan tanpa argumen atau --config=/path/ke/config.php.');
    }
}

$host = '';
$database = '';
$username = '';
$databasePassword = '';

if ($configPath !== null) {
    if ($configPath === '' || !is_file($configPath) || !is_readable($configPath)) {
        fail('File konfigurasi tidak dapat dibaca.');
    }
    $cfg = require $configPath;
    if (is_array($cfg)) {
        if (isset($cfg['database']) && is_array($cfg['database'])) {
            $host = isset($cfg['database']['hostname']) ? trim((string) $cfg['database']['hostname']) : '';
            $database = isset($cfg['database']['database']) ? trim((string) $cfg['database']['database']) : '';
            $username = isset($cfg['database']['username']) ? trim((string) $cfg['database']['username']) : '';
            $databasePassword = isset($cfg['database']['password']) ? (string) $cfg['database']['password'] : '';
        } elseif (isset($db['default']) && is_array($db['default'])) {
            $host = isset($db['default']['hostname']) ? trim((string) $db['default']['hostname']) : '';
            $database = isset($db['default']['database']) ? trim((string) $db['default']['database']) : '';
            $username = isset($db['default']['username']) ? trim((string) $db['default']['username']) : '';
            $databasePassword = isset($db['default']['password']) ? (string) $db['default']['password'] : '';
        }
    }
} else {
    $prodDbFile = dirname(__DIR__) . '/application/config/production/database.php';
    $baseDbFile = dirname(__DIR__) . '/application/config/database.php';

    if (!defined('BASEPATH')) {
        define('BASEPATH', dirname(__DIR__) . '/system/');
    }

    if (is_file($prodDbFile) && is_readable($prodDbFile)) {
        require $prodDbFile;
    } elseif (is_file($baseDbFile) && is_readable($baseDbFile)) {
        require $baseDbFile;
    }

    if (isset($db['default']) && is_array($db['default'])) {
        $host = isset($db['default']['hostname']) ? trim((string) $db['default']['hostname']) : '';
        $database = isset($db['default']['database']) ? trim((string) $db['default']['database']) : '';
        $username = isset($db['default']['username']) ? trim((string) $db['default']['username']) : '';
        $databasePassword = isset($db['default']['password']) ? (string) $db['default']['password'] : '';
    }
}

if ($host === '') {
    $host = prompt('Database Host', 'localhost');
}
if ($database === '') {
    $database = prompt('Database Name', 'ami');
}
if ($username === '') {
    $username = prompt('Database Username', 'root');
}
if ($databasePassword === '') {
    $databasePassword = prompt('Database Password', '', true);
}

$name = prompt('Nama super-admin');
$email = strtolower(prompt('Email super-admin'));
$password = prompt('Password super-admin (minimal 12 karakter)', null, true);
$confirmation = prompt('Ulangi password super-admin', null, true);

if ($host === '' || $database === '' || $username === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fail('Input tidak valid. Tidak ada perubahan database.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = null;
$transaction = false;
$lockAcquired = false;
try {
    $db = mysqli_init();
    mysqli_real_connect($db, $host, $username, $databasePassword, $database);
    mysqli_set_charset($db, 'utf8mb4');
    $lock = $db->query("SELECT GET_LOCK('ami.bootstrap_super_admin', 10) AS acquired")->fetch_assoc();
    if ((int) ($lock['acquired'] ?? 0) !== 1) {
        throw new RuntimeException('Bootstrap sedang diproses operator lain. Tidak ada perubahan database.');
    }
    $lockAcquired = true;
    $db->begin_transaction();
    $transaction = true;

    $role = 'super_admin';
    $statement = $db->prepare('SELECT COUNT(*) AS total FROM users WHERE role = ? FOR UPDATE');
    $statement->bind_param('s', $role);
    $statement->execute();
    $total = (int) $statement->get_result()->fetch_assoc()['total'];
    $statement->close();
    if ($total !== 0) {
        throw new RuntimeException('Super-admin sudah ada; bootstrap dibatalkan.');
    }

    $statement = $db->prepare('SELECT COUNT(*) AS total FROM users WHERE email = ? FOR UPDATE');
    $statement->bind_param('s', $email);
    $statement->execute();
    $exists = (int) $statement->get_result()->fetch_assoc()['total'];
    $statement->close();
    if ($exists !== 0) {
        throw new RuntimeException('Email sudah digunakan; bootstrap dibatalkan.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('Hash password gagal dibuat.');
    }
    $statement = $db->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
    $statement->bind_param('ssss', $name, $email, $hash, $role);
    $statement->execute();
    if ($statement->affected_rows !== 1) {
        throw new RuntimeException('Akun tidak dapat dibuat.');
    }
    $statement->close();

    $db->commit();
    $transaction = false;
    fwrite(STDOUT, 'Super-admin berhasil dibuat.' . PHP_EOL);
} catch (Throwable $e) {
    if ($transaction && $db instanceof mysqli) {
        $db->rollback();
    }
    fail('Gagal bootstrap super-admin: ' . $e->getMessage());
} finally {
    if ($lockAcquired && $db instanceof mysqli) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $db->query("SELECT RELEASE_LOCK('ami.bootstrap_super_admin')");
    }
    if ($db instanceof mysqli) {
        $db->close();
    }
}
