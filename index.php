<?php
namespace Fredao;

require_once './routes.php';
require_once './database.php';

use Database;

enum Position: string
{
    case User = 'USER';
    case Admin = 'ADMIN';
}

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$database = new Database();

Router\allow_cors();
Router\run($database);
