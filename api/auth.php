<?php
namespace Fredao\Auth;

use Fredao\Position;

final class UserSessionFields
{
    public const IS_LOGGED = 'isLogged';
    public const USERNAME = 'username';
    public const PASSWORD = 'password';
}

function init_session(
    string $username,
    #[\SensitiveParameter] string $password,
    Position $position,
): void {
    // REVIEW - make the field a reference to the $_SESSION
    $_SESSION[UserSessionFields::IS_LOGGED] = true;
    $_SESSION[UserSessionFields::USERNAME] = $username;
    $_SESSION[UserSessionFields::PASSWORD] = $password;
    $_SESSION[Position::class] = $position->value;
}

function is_logged(): bool
{
    return isset($_SESSION['isLogged']);
}

function is_validated(Position $level): bool
{
    return isset($_SESSION[Position::class]) && $_SESSION[Position::class] == Position::User->value;
}

function get_session_data(): array 
{
    return array(
        "user" => $_SESSION[UserSessionFields::USERNAME],
        "password" => $_SESSION[UserSessionFields::PASSWORD],
        "session-id" => session_id(),
    );
}