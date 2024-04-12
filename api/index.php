<?php // TODO - Implement ENV
declare(strict_types= 1);

namespace Fredao;

require_once  __DIR__ . '/routes/routes.php';
require_once  __DIR__ . '/database.php';

use Database;

enum Position: string
{
    case User = 'USER';
    case Admin = 'ADMIN';
}

date_default_timezone_set('America/Sao_Paulo');
session_start();

$env = parse_ini_file('.env');

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$database = new Database();

Router\allow_cors();
Router\run($database);
