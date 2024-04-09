<?php
namespace Fredao;

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
        int $status_code,
        string $message = ''
    ): void {
        $http_status_codes = [
            400 => "Bad Request",
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            422 => "Unprocessable Entity",
            201 => "Created",
            200 => "OK",
            204 => "No Content",
            500 => "Internal Server Error",
            // ...
        ];

        $status_text = $http_status_codes[$status_code] ?? "Unknown Status";

        header("HTTP/1.0 $status_code $status_text");
        echo "$status_code $status_text: $message";
    }

    public static function not_found(): void
    {
        self::build_response(404, 'not found');
    }
}