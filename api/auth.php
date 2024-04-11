<?php
/**
 * Authentication Proccess
 * - Store for each user a hash with a certain validity that refers to a static user identifier data
 * - Send this hash to the client and store it either in the local or session storage
 * - Now for each client call to the api that requires validation we internally check if both hashes matches
 * 
 * The key and the iv should be the same both for the encryption and decryption
 * it might be good to write a script that for every n days, refresh both iv and passphrase values and write 
 * them into a .env file so we can read in the server 
 * hash layout sha256(user_id + exp_date)
 */

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

function encrypt() 
{

}

function decrypt()
{

}