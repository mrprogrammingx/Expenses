<?php

declare(strict_types=1);

use App\Enum\StorageDriver;
use App\Enum\AppEnvironment;

$appEnv = $_ENV['APP_ENV'] ?? AppEnvironment::Production->value;
$appSnakeName = strtolower(str_replace(' ', '_', $_ENV['APP_NAME']));

return [
    'app_key' => $_ENV['APP_KEY'] ?? '',
    'app_name' => $_ENV['APP_NAME'],
    'app_version' => $_ENV['APP_VERSION'],
    'app_url' => $_ENV['APP_URL'],
    'app_timezone' => $_ENV['APP_TIMEZONE'],
    'app_environment' => $appEnv,
    'display_error_details' => (bool) ($_ENV['APP_DEBUG'] ?? 0),
    'log_errors' => true,
    'log_error_details' => true,
    'doctrine' => [
        'dev_mode' => AppEnvironment::isDevelopment($appEnv),
        'cache_dir' => STORAGE_PATH . '/cache/doctrine',
        'entity_dir' => [APP_PATH . '/Entity'],
        'connection' => [
            'driver' => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
        ],
    ],
    'session' => [
        'name' => $appSnakeName . "_session",
        'flashName' => $appSnakeName . "_flash",
        'secure' => true,
        'httponly' => true,
        'samesite' => 'lax'
    ],
    'storage' => [
        'driver' => StorageDriver::Remote_AWS,
        's3' => [
            'key' => $_ENV['AWS_KEY'],
            'secret' => $_ENV['AWS_SECRET'],
            'region' => $_ENV['AWS_REGION'],
            'bucket' => $_ENV['AWS_BUCKET'],
        ]
    ],
    'mailer' => [
        'dsn' => $_ENV['MAILER_DSN'],
        'from' => $_ENV['MAILER_FROM'],
    ],
    'redis' => [
        'host' => $_ENV['REDIS_HOST'],
        'port' => $_ENV['REDIS_PORT'],
        'password' => $_ENV['REDIS_PASSWORD'],
    ],
    'trusted_proxies' => [],
];