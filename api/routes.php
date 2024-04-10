<?php
namespace Fredao\Router;

require_once './http.php';
require_once './auth.php';
require_once './user.model.php';
require_once './user.routes.php';

use Fredao\Http;
use Model\UserModel;
use Fredao\Auth;
use Fredao\Position;

$url_array = array();

function allow_cors(): void
{
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS, HEAD');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
    header('Access-Control-Max-Age: 86400');

    // Exit early so the page isn't fully loaded for options requests
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
        exit();
    }
}

function run($databaseConn): void
{
    global $url_array;
    header("Content-type: application/json");
    allow_cors();

    $method = $_SERVER['REQUEST_METHOD'];

    $url_array = explode('/', $_SERVER['REQUEST_URI']);

    // Removes the two first indicies from the array (/fredao/api/)
    array_shift($url_array);
    array_shift($url_array);

    match ($url_array[1]) {
        'user' => user_routes($method, new UserModel($databaseConn), $url_array),
        'image' => image_route($method, new UserModel($databaseConn)),
        '' => fredao_route($method),
        default => Http::not_found(),
    };
}

function image_route(string $method, UserModel $model) 
{
    global $url_array;

    switch ($method) {
        case Http::GET: 
            if (Auth\is_validated(Position::Admin)) {}
            $id = get_id_in_url($model);
            if (!$id) {
                return;
            }

            $result = $model->get_by_id($id);
            if (!$result) {
                Http::build_response(404, 'could not find user with given id');
                
                return;
            }

            $base64_image = base64_encode($result->profilePic);
            Http::build_response(200, array("image" => $base64_image));
            break;

        // TODO - make it so that is only possible to change you own profile pic (if not adm)
        case Http::PUT:
            $id = get_id_in_url($model);
            if (!$id) {
                return;
            }

            $body = file_get_contents('php://input');
            $decoded = json_decode($body, true);
            
            $image = base64_decode($decoded['data'], true);
            if (!$image) {
                Http::build_response(422, "invalid image");

                return;
            }

            if (!Auth\is_logged()) {
                Http::build_response(401);

                return;
            }

            $model->id = $id;
            $model->profilePic = $image;
            if (!$model->update_image()) {
                Http::build_response(500, "failed to upload image");

                return;
            }

            Http::build_response(204);
            return;

        default: Http::not_found();
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

    echo json_encode(array('hello' => 'fredao'));
}

function get_id_in_url(UserMoDel $model): int|bool {
    global $url_array;

    if (empty($url_array[2])) {
        Http::build_response(400, "Invalid ID");

        return false;
    }

    $id = intval($url_array[2]);
    if (!$model->get_by_id($id)) {
        Http::not_found();

        return false;
    }

    return $id;
}