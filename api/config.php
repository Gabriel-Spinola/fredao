<?php declare(strict_types=1);
$env = parse_ini_file('.env');

define("DEV", 0);
define("PROD", 1);

if (!get_browser()) {
    define("ENVIRONMENT", $env["ENV"] === "DEV" ? DEV : PROD);
} else {
    define("ENVIRONMENT", PROD);
}

if (ENVIRONMENT === DEV) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
} else {
    error_reporting(E_ERROR | E_PARSE);
}

date_default_timezone_set('America/Sao_Paulo');