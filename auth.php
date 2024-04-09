<?php
namespace Fredao\Auth;

use Fredao\Position;

final class UserSessionFields
{
    public const isLogged = 'isLogged';
    public const username = 'username';
    public const password = 'password';
}

function init_session(
    string $username,
    #[\SensitiveParameter] string $password,
    Position $position,
): void {
    // REVIEW - make the field a reference to the $_SESSION
    $_SESSION[UserSessionFields::isLogged] = true;
    $_SESSION[UserSessionFields::username] = $username;
    $_SESSION[UserSessionFields::password] = $password;
    $_SESSION[Position::class] = $position->value;
}

function is_logged(): bool
{
    return isset($_SESSION['isLogged']);
}

function is_validated()
{
    return isset($_SESSION[Position::class]) && $_SESSION[Position::class] == Position::User->value;
}

function get_session_data(): array {
    return array(
        "user" => $_SESSION[UserSessionFields::username],
        "password" => $_SESSION[UserSessionFields::password],
    );
} 
