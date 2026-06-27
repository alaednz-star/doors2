<?php

// Resolve DB credentials in this order:
//   1. config/database.local.php  (gitignored — used on shared hosts like
//      InfinityFree where env vars aren't available; just fill in & upload it)
//   2. environment variables       (generic DB_* or Railway MYSQL*)
//   3. local LAMPP/XAMPP defaults
$local = __DIR__ . '/database.local.php';
if (is_file($local)) {
    return require $local;
}

$env = static fn (array $keys, string $default): string => (static function () use ($keys, $default) {
    foreach ($keys as $k) {
        $v = getenv($k);
        if ($v !== false && $v !== '') {
            return $v;
        }
    }
    return $default;
})();

return [
    'host'    => $env(['DB_HOST', 'MYSQLHOST', 'MYSQL_HOST'], '127.0.0.1'),
    'port'    => $env(['DB_PORT', 'MYSQLPORT', 'MYSQL_PORT'], '3306'),
    'name'    => $env(['DB_NAME', 'MYSQLDATABASE', 'MYSQL_DATABASE'], 'door_showroom'),
    'user'    => $env(['DB_USER', 'MYSQLUSER', 'MYSQL_USER'], 'root'),
    'pass'    => $env(['DB_PASS', 'MYSQLPASSWORD', 'MYSQL_PASSWORD'], ''),
    'charset' => $env(['DB_CHARSET'], 'utf8mb4'),
];
