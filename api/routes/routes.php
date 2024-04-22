<?php declare(strict_types=1);
/**
 * ! Authenticated routes require the user token as first parameter
 * REVIEW - Using '|' to replace '/' in the url params
 */

/// TODO - RouterFactory
namespace Fredao\Router;

require_once __DIR__ . "/../http.php";
require_once __DIR__ . "/../models/user.model.php";
require_once __DIR__ . "/../models/news.model.php";
require_once __DIR__ . "/user.routes.php";
require_once __DIR__ . "/news.routes.php";
require_once __DIR__ . "/../authentication/auth.php";
require_once __DIR__ . "/../authentication/crypt.php";

use Fredao\Http;
use Model\NewsModel;
use Model\UserModel;
use Fredao\Auth;
use Fredao\StatusCode;
use function Fredao\Router\News\news_routes;
use function Fredao\Router\User\user_routes;

$url_array = array();

function allow_cors(): void
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS, HEAD");
    header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
    header("Access-Control-Max-Age: 86400");

    // Exit early so the page isn't fully loaded for options requests
    if (strtolower($_SERVER["REQUEST_METHOD"]) == "options") {
        exit();
    }
}

function run($databaseConn): void
{
    global $url_array;

    allow_cors();
    header("Content-type: application/json");

    $method = $_SERVER["REQUEST_METHOD"];
    $url_array = explode("/", $_SERVER["REQUEST_URI"]);

    // Removes the two first indicies from the array (/fredao/api/)
    array_shift($url_array);
    array_shift($url_array);

    match ($url_array[1]) {
        "user" => user_routes($method, new UserModel($databaseConn), $url_array),
        "news" => news_routes($method, new NewsModel($databaseConn), $url_array),
        "image" => image_route($method, new UserModel($databaseConn)),
        "auth" => auth_routes($method, new UserModel($databaseConn)),
        "" => fredao_route($method),
        default => Http::not_found(),
    };
}

function auth_routes(string $method, UserModel $model): void
{
    if ($method !== Http::POST) {
        Http::build_response(StatusCode::METHOD_NOT_ALLOWED);

        return;
    }

    $body = file_get_contents("php://input");
    if (!$body || strlen($body) < 1) {
        Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, "Body should not be empty");

        return;
    }

    $decoded = json_decode($body, true);

    $key = $decoded["key"];
    if (!isset($key)) {
        Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, "Key is not optional");

        return;
    }

    match (Auth\validate_user($key, $model)) {
        StatusCode::INTERNAL_SERVER_ERROR => Http::build_response(StatusCode::INTERNAL_SERVER_ERROR, "Failed to decrypt"),
        StatusCode::UNAUTHORIZED => Http::build_response(StatusCode::UNAUTHORIZED, "Login expired"),
        StatusCode::NOT_FOUND => Http::build_response(StatusCode::NOT_FOUND, "User not found"),
        StatusCode::UNPROCESSABLE_ENTITY => Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, array("Failed to proccess the user data", $key)),

        default => Http::build_response(StatusCode::OK, "User's valid"),
    };
}

function image_route(string $method, UserModel $model)
{
    switch ($method) {
        case Http::GET:
            $token = ""; 
            if (!get_param_from_url($token, 0)) {
                return;
            }

            $id = null;
            if (!Auth\validate_user($token, $model, $id)) {
                Http::build_response(StatusCode::UNAUTHORIZED);
                
                return;
            }

            if ($id === null) {
                Http::build_response(StatusCode::UNAUTHORIZED);
                
                return;
            }

            $result = $model->get_by_id($id);
            if (!$result) {
                Http::build_response(StatusCode::NOT_FOUND, "could not find user with given id");

                return;
            }

            $base64_image = base64_encode($result->profilePic);
            $response = array("image" => $result->profilePic);

           // echo json_encode($response);
            Http::build_response(StatusCode::OK, $response);
            break;

        case Http::PUT:
            $token = ""; 
            if (!get_param_from_url($token, 0)) {
                return;
            }

            $id = null;
            if (!Auth\validate_user($token, $model, $id)) {
                Http::build_response(StatusCode::UNAUTHORIZED);
                
                return;
            }

            if ($id === null) {
                Http::build_response(StatusCode::UNAUTHORIZED);
                
                return;
            }

            $body = file_get_contents("php://input");
            $decoded = json_decode($body, true);

            $image = $decoded["data"];
            if (!$image || $image === '') {
                Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, "invalid image");

                return;
            }

            $model->id = $id;
            $model->profilePic = $image;
            if (!$model->update_image()) {
                Http::build_response(StatusCode::INTERNAL_SERVER_ERROR, "failed to upload image");

                return;
            }

            Http::build_response(StatusCode::OK);
            return;

        default:
            Http::not_found();
    }
}

/**
 * @param string
 * @param bool|string[] 
 */
function fredao_route(string $method): void
{
    if ($method != Http::GET) {
        Http::not_found();

        return;
    }

    echo json_encode(array("hello" => "fredao"));
}

/**
 * @param int start from 0, being 0 the first param
 */
function get_param_from_url(string &$param, int $index = 0): bool
{
    global $url_array;

    if (empty($url_array[$index + 2])) {
        Http::build_response(StatusCode::BAD_REQUEST, "Invalid parameters");

        return false;
    }

    $param = str_replace("%7C", "/", $url_array[$index + 2]);
    return true;
}

function get_id_in_url(UserMoDel $model, int $index = 0): int|bool
{
    global $url_array;

    $param = "";
    if(!get_param_from_url($param, $index)) {
        return false;
    }

    $id = intval($param);
    if (!$model->get_by_id($id)) {
        Http::not_found();

        return false;
    }

    return $id;
}

