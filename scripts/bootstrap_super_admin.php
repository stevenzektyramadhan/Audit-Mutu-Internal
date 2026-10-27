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

if (count($argv) > 2 || (isset($argv[1]) && strpos($argv[1], '--config=') !== 0)) {
    fail('Gunakan tanpa argumen atau --config=/etc/ami/production.php.');
}

$configPath = isset($argv[1]) ? substr($argv[1], strlen('--config=')) : '/etc/ami/production.php';
if ($configPath === '' || $configPath[0] !== '/' || !is_file($configPath) || !is_readable($configPath) || (fileperms($configPath) & 0022) !== 0) {
    fail('File konfigurasi production tidak dapat dibaca dengan aman.');
}

$productionConfig = require $configPath;
$databaseConfig = is_array($productionConfig) && isset($productionConfig['database']) && is_array($productionConfig['database'])
    ? $productionConfig['database']
    : array();
$host = isset($databaseConfig['hostname']) ? trim((string) $databaseConfig['hostname']) : '';
$database = isset($databaseConfig['database']) ? trim((string) $databaseConfig['database']) : '';
$username = isset($databaseConfig['username']) ? trim((string) $databaseConfig['username']) : '';
$databasePassword = isset($databaseConfig['password']) ? (string) $databaseConfig['password'] : '';
$name = prompt('Nama super-admin');
$email = strtolower(prompt('Email super-admin'));
$password = prompt('Password super-admin (minimal 12 karakter)', null, true);
$confirmation = prompt('Ulangi password super-admin', null, true);

if ($host === '' || $database === '' || $username === '' || $databasePassword === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !hash_equals($password, $confirmation)) {
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
    fwrite(STDOUT, "Super-admin awal berhasil dibuat. Tidak ada credential ditampilkan.\n");
} catch (Throwable $exception) {
    if ($db instanceof mysqli && $transaction) {
        $db->rollback();
    }
    fail('Bootstrap gagal: ' . $exception->getMessage());
} finally {
    $password = $confirmation = $databasePassword = '';
    if ($db instanceof mysqli) {
        if ($lockAcquired) {
            mysqli_report(MYSQLI_REPORT_OFF);
            $db->query("SELECT RELEASE_LOCK('ami.bootstrap_super_admin')");
        }
        $db->close();
    }
}
