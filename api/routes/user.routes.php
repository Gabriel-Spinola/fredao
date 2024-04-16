<?php
/**
 * ! Authenticated routes require the user token as first parameter
 */

namespace Fredao\Router;

require_once __DIR__ . "/../http.php";
require_once __DIR__ . "/../authentication/auth.php";
require_once __DIR__ . "/../models/user.model.php";

use Fredao\Auth;
use Model\UserModel;
use Fredao\Http;
use Fredao\StatusCode;

function user_routes(string $method, UserModel $model, array $url_array): void
{
    match ($method) {
        Http::GET => handle_get(),
        Http::POST => handle_post($model, $url_array),
        Http::PUT => handle_put(),
        Http::DELETE => handle_delete($model, $url_array),

        default => Http::build_response(StatusCode::METHOD_NOT_ALLOWED, "method not allowed"),
    };
}

function handle_get(): void
{
    Http::build_response(StatusCode::OK);
}

function handle_post(UserModel $model, array $url_array)
{
    $body = file_get_contents("php://input");
    $decoded = json_decode($body, true);

    $username = $decoded["username"];
    $password = $decoded["password"];
    if (!isset($username) || !isset($password)) {
        Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, "Unable to proccess body");

        return;
    }

    $model->username = $username;
    $model->password = $password;
    if ($url_array[2] == "login") {
        $account = $model->get_by_account();
        if ($account == null) {
            Http::build_response(StatusCode::NOT_FOUND, "Usuário não encontrado ou não existe");

            return;
        }

        $encrypted_token = Auth\create_user_token($account->id);
        if (!$encrypted_token) {
            Http::build_response(StatusCode::INTERNAL_SERVER_ERROR, "failed to create user session/token " . $encrypted_token);

            return;
        }

        Http::build_response(StatusCode::OK, $encrypted_token);
        return;
    }

    $ok = $model->insert();
    if (!$ok) {
        Http::build_response(StatusCode::INTERNAL_SERVER_ERROR, "Failed to insert user data into database");

        return;
    }

    Http::build_response(StatusCode::CREATED, "Created session\n$username");
}

function handle_put()
{
    $body = file_get_contents("php://input");
    $decoded = json_decode($body, true);

    $username = $decoded["username"];
    $password = $decoded["password"];
    if (!isset($username) || !isset($password)) {
        Http::build_response(StatusCode::UNPROCESSABLE_ENTITY, "Unable to proccess body");

        return;
    }
}

function handle_delete(UserModel $model, array $url_array)
{
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

    if (!$model->delete($id)) {
        Http::build_response(StatusCode::OK, "operation succedded but nothing got deleted");

        return;
    }

    Http::build_response(StatusCode::NO_CONTENT);
}