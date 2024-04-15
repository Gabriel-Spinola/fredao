<?php
namespace Fredao;

enum StatusCode: int
{
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case UNPROCESSABLE_ENTITY = 422;
    case CREATED = 201;
    case OK = 200;
    case NO_CONTENT = 204;
    case INTERNAL_SERVER_ERROR = 500;
    // ...
}

final class Http
{
    public const REQ_METHOD = "REQUEST_METHOD";

    public const POST = "POST";
    public const GET = "GET";
    public const PUT = "PUT";
    public const DELETE = "DELETE";

    public static function checkMethod(string $targetMethod): bool
    {
        return false;
    }

    public static function build_response(
        StatusCode $status_code,
        string|array $message = ""
    ): void {
        $status_text = match ($status_code) {
            StatusCode::BAD_REQUEST => "Bad Request",
            StatusCode::UNAUTHORIZED => "Unauthorized",
            StatusCode::FORBIDDEN => "Forbidden",
            StatusCode::NOT_FOUND => "Not Found",
            StatusCode::METHOD_NOT_ALLOWED => "Method Not Allowed",
            StatusCode::UNPROCESSABLE_ENTITY => "Unprocessable Entity",
            StatusCode::CREATED => "Created",
            StatusCode::OK => "OK",
            StatusCode::NO_CONTENT => "No Content",
            StatusCode::INTERNAL_SERVER_ERROR => "Internal Server Error",
        };

        header("HTTP/1.0 {$status_code->value} $status_text");
        echo json_encode(array("status" => $status_code->value, "message" => $message));
    }

    public static function not_found(): void
    {
        self::build_response(StatusCode::NOT_FOUND, "not found");
    }
}