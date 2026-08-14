// <?php
// // ./config/bootstrap.php

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// date_default_timezone_set('Asia/Phnom_Penh');

// require_once __DIR__ . '/../vendor/autoload.php';

// use Dotenv\Dotenv;

// $envPath = dirname(__DIR__);

// if (file_exists($envPath . '/.env')) {
//     Dotenv::createImmutable($envPath)->load();
// }

// require_once __DIR__ . '/db.php';
// require_once __DIR__ . '/app.php';

// require_once __DIR__ . '/../helpers/request.php';
// require_once __DIR__ . '/../helpers/response.php';
// require_once __DIR__ . '/../helpers/upload.php';
// require_once __DIR__ . '/../helpers/csrf.php';
// generateCSRF();

// require_once __DIR__ . '/../core/DB.php';
// require_once __DIR__ . '/../core/ORM.php';
// require_once __DIR__ . '/../core/Cache.php';

// require_once __DIR__ . '/../components/VercelAnalytics.php';
// require_once __DIR__ . '/../components/VercelSpeedInsights.php';

// config/bootstrap.php

date_default_timezone_set('Asia/Phnom_Penh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Safe load .env (won't crash if .env file is missing)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

if (!empty($_ENV['APP_TIMEZONE'])) {
    date_default_timezone_set($_ENV['APP_TIMEZONE']);
}

// Config & Database
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/db.php';

// Helpers
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../helpers/csrf.php';

// Core
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/ORM.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../core/Logger.php';

// Authentication (UNCOMMENTED - Required for checkAuth())
if (file_exists(__DIR__ . '/../app/api/v1/auth.php')) {
    require_once __DIR__ . '/../app/api/v1/auth.php';
}
