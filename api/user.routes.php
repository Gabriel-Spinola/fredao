<?php
namespace Fredao\Router;

require_once './http.php';
require_once './auth.php';
require_once './user.model.php';

use Fredao\Auth;
use Fredao\Position;
use Model\UserModel;
use Fredao\Http;

function user_routes(string $method, UserModel $model, array $url_array): void
{
    match ($method) {
        Http::GET => handle_get(),
        Http::POST => handle_post($model, $url_array),
        Http::PUT => handle_put(),
        Http::DELETE => handle_delete($model, $url_array),
        
        default => Http::build_response(405, "method not allowed"),
    };
}

function handle_get(): void
{
    Http::build_response(
        200,
        array("logged" => Auth\is_logged(), ...Auth\get_session_data())
    );
}

function handle_post(UserModel $model, array $url_array)
{
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
        if (isset($_SESSION['isLogged'])) {
            if (!session_destroy()) {
                Http::build_response(500, "Failed to destroy previus session");

                return;
            }
        }

        $account = $model->get_by_account();

        if ($account != null) {
            Auth\init_session($username, $password, Position::User);
            Http::build_response(200, strval(session_id()));

            return;
        } 

        Http::build_response(404, "Usuário não encontrado ou não existe");
        return;
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
}

function handle_put() 
{
    $body = file_get_contents('php://input');
    $decoded = json_decode($body, true);

    $username = $decoded['username'];
    $password = $decoded['password'];
    if (!isset($username) || !isset($password)) {
        Http::build_response(422, "Unable to proccess body");

        return;
    }
}

function handle_delete(UserModel $model, array $url_array) 
{
    if (!Auth\is_validated(Position::Admin)) {
        Http::build_response(401);

        return;
    }

    if (empty($url_array[2])) {
        Http::build_response(400, "Invalid ID");

        return;
    }

    $id = intval($url_array[2]);
    if (!$model->get_by_id($id)) {
        Http::not_found();

        return;
    }

    if (!$model->delete($id)) {
        Http::build_response(200, "operation succedded but nothing got deleted");

        return;
    }

    Http::build_response(204);
}