<?php declare(strict_types=1);

define("ERROR_REPORTING", 1);

if (ERROR_REPORTING === 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
} else {
    error_reporting(E_ERROR | E_PARSE);
}

date_default_timezone_set('America/Sao_Paulo');