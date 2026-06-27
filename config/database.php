<?php

// Accept generic DB_* vars or Railway's MYSQL* vars; fall back to local LAMPP.
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
