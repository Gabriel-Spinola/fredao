<?php 
namespace Fredao\Router;

require_once './http.php';

use Fredao\Position;
use Fredao\Http;

function allow_cors(): void {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Content-type: application/json");
}

function routes(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

    $url_array = explode('/', $_SERVER['REQUEST_URI']);
    array_shift($url_array);
    array_pop($url_array);

 
    foreach ($url_array as $key => $value) {
        echo $key .' '. $value .' ';
    }

    match ($url_array[0]) {
        'user' => user_routes(),
        default => fredao_route($method, $request),
    };
}

function user_routes() {
    echo json_encode(array("user" => "route"));
}

function is_validated() {
    return isset($_SESSION[Position::class]) && $_SESSION[Position::class] == Position::User->value;
}

/**
 * @param string
 * @param bool|string[] 
 */
function fredao_route(string $method, bool|array $request): void {
    if ($method != Http::GET) {
        header("HTTP/1.0 404 Not Found");
        echo "404 not found";

        return;
    }

    echo json_encode(array('hello' => 'fredao', 'req' => $request));
}