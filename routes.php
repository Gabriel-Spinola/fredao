<?php
namespace Fredao\Router;

require_once './http.php';
require_once './auth.php';
require_once './user.model.php';

use Fredao\Position;
use Fredao\Http;
use Fredao\Auth;
use Model\UserModel;

$url_array = array();

function allow_cors(): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Content-type: application/json");
}

function run($databaseConn): void
{
    global $url_array;

    $method = $_SERVER['REQUEST_METHOD'];
    $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

    $url_array = explode('/', $_SERVER['REQUEST_URI']);
    array_shift($url_array);

    match ($url_array[1]) {
        'user' => user_routes($method, new UserModel($databaseConn)),
        '' => fredao_route($method, $request),
        default => Http::not_found(),
    };
}

// In theory thats not rest
// in rest there's no session, and also the pararameters are not implemented
function user_routes(string $method, UserModel $model): void
{
    global $url_array;

    switch ($method) {
        case Http::GET:
            Http::build_response(
                200,
                array("logged" => Auth\is_logged(), ...Auth\get_session_data())
            );
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

            $model->username = $username;
            $model->password = $password;
            if ($url_array[2] == 'login') {
                $account = $model->getByAccount();

                if ($account != null) {
                    Auth\init_session($username, $password, Position::User);
                    Http::build_response(200);

                    return;
                }
            }

            if ($url_array[2] == 'logout') {
                if (!session_destroy()) {
                    Http::build_response(500, "failed to destroy current session");
    
                    return;
                }
    
                Http::build_response(204);
                return;
            }

            if (!$model->insert()) {
                Http::build_response(500, "Failed to insert user data into database");

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

            Http::build_response(204);
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
