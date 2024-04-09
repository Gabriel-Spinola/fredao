<?php
namespace Fredao\Router;

require_once './http.php';
require_once './auth.php';

use Fredao\Position;
use Fredao\Http;
use Fredao\Auth;

function allow_cors(): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Content-type: application/json");
}

function run(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

    $url_array = explode('/', $_SERVER['REQUEST_URI']);
    array_shift($url_array);

    match ($url_array[1]) {
        'user' => user_routes($method),
        '' => fredao_route($method, $request),
        default => Http::not_found(),
    };
}

function user_routes(string $method): void
{
    switch ($method) {
        case Http::GET:
            echo json_encode(array("logged" => Auth\is_logged()));
            Http::build_response(200);
            return;

        case Http::POST:
            $body = file_get_contents('php://input');
            $decoded = json_decode($body, true);

            $username = $decoded['username'];
            $password = $decoded['password'];
            if (!isset($username) || !isset($password)) {
                Http::build_response(422, "Unable to proccess body");

                return;
            }

            Auth\init_session($username, $password, Position::User);
            Http::build_response(201, "Created session\n$username: $password");
            return;

        case Http::DELETE:
            // TODO - get id
            if (!session_destroy()) {
                Http::build_response(500, "failed to destroy current session");

                return;
            }

            Http::build_response(204, "");
            return;

        case Http::PUT:
            $body = file_get_contents('php://input');
            $decoded = json_decode($body, true);

            $username = $decoded['username'];
            $password = $decoded['password'];
            if (!isset($username) || !isset($password)) {
                Http::build_response(422, "Unable to proccess body");

                return;
            }
            return;

        default:
            Http::build_response(405, "method not allowed");

            return;
    }
}

/**
 * @param string
 * @param bool|string[] 
 */
function fredao_route(string $method, bool|array $request): void
{
    if ($method != Http::GET) {
        Http::not_found();

        return;
    }

    echo json_encode(array('hello' => 'fredao', 'req' => $request));
}
