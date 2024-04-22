<?php // TODO - Implement ENV
declare(strict_types=1);

namespace Fredao;

require_once __DIR__ . '/routes/routes.php';
require_once __DIR__ . '/database.php';

use Database;

enum Position: string
{
    case User = 'USER';
    case Admin = 'ADMIN';
}

$env = parse_ini_file('.env');

$database = new Database();

Router\allow_cors();
Router\run($database);
