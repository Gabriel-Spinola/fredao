<?php
/**
 * Authentication Proccess
 * - Store for each user a token with a certain validity that refers to a static user identifier data
 * - Send this token to the client and store it either in the local or session storage
 * - Now for each client call to the api that requires validation we internally check if both hashes matches
 * 
 * The key and the iv should be the same both for the encryption and decryption
 * it might be good to write a script that for every n days, refresh both iv and passphrase values and write 
 * them into a .env file so we can read in the server 
 * 
 * user_id = 4 bytes long
 * hash layout sha256(user_id + exp_date) maybe? (+ hash(password || email || unique_id))
 */

namespace Fredao\Auth;

require_once __DIR__ . "/crypt.php";
require_once __DIR__ . "/../models/user.model.php";

use Fredao\Position;
use Fredao\Crypt\Crypt;
use Model\UserModel;

define("USER_ID_BYTES_LENGTH", 4);
define("EXP_DATE_BYTES_LENGTH", 25);

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

/**
 * @param int $id the target user id. this id will be converted to a '0000' trailing format, so it can fit into the encryption bytes length requirement
 * @return string|bool the encrypted token on success or false on failure
 */
function create_user_token(int $id): string|bool 
{
    $formatted_id = str_pad((string) $id, USER_ID_BYTES_LENGTH, '0', STR_PAD_LEFT);
    if (strlen($formatted_id) === USER_ID_BYTES_LENGTH) {
        return false;
    }

    $exp_offset = ' + 1 days';

    $today = date(DATE_ATOM);
    $exp_time = strtotime($today . $exp_offset);
    if (!$exp_offset) {
        return false;
    }

    $exp_date = date(DATE_ATOM, $exp_time);
    if (strlen($exp_date) !== EXP_DATE_BYTES_LENGTH) {
        return false;
    }

    $target_data = $formatted_id . $exp_date;

    return Crypt::encrypt($target_data);
}

/**
 * both user id & exp_date must conform to their corresponding bytes length rules defined at the top of the file
 * @return int proccess status code
 * Validates the given key. For now the validations procces is: 1st. check if the token is not expired; 2nd; check if the extracted id correspond to a user in the database
 */
function validate_user(string $key, UserModel $model): int
{
    $decrypted = Crypt::decrypt($key);
    if (!$decrypted) {
        return 500;
    }

    $id_buffer = '';
    for ($i = 0; $i < USER_ID_BYTES_LENGTH; $i++) {
        $id_buffer .= $decrypted[$i];
    }

    $id = (string) ((int) $id_buffer);
   
    $offset = USER_ID_BYTES_LENGTH;
    $exp_date_buffer = ''; 
    for ($i = 0; $i < EXP_DATE_BYTES_LENGTH; $i++) {
        $exp_date_buffer .= $decrypted[$i + $offset];
    }

    $exp_date = strtotime($exp_date_buffer);
    if (!$exp_date) {
        return 422;
    }
    
    $today = strtotime(date(DATE_ATOM));
    if (!$today) {
        return 422;
    }

    if ($today > $exp_date) {
        return 401;
    }

    if (!$model->get_by_id($id)) {
        return 404;
    }

    return 200;
}