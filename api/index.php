<?php // TODO - Implement ENV
declare(strict_types= 1);

namespace Fredao;

require_once  __DIR__ . '/routes/routes.php';
require_once  __DIR__ . '/database.php';

use Database;

define("ERROR_REPORTING", 0);

enum Position: string
{
    case User = 'USER';
    case Admin = 'ADMIN';
}

date_default_timezone_set('America/Sao_Paulo');
session_start();

$env = parse_ini_file('.env');

if (ERROR_REPORTING === 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
} 
else {
    error_reporting(E_ERROR | E_PARSE);
}

$database = new Database();

Router\allow_cors();
Router\run($database);
