<?php
namespace Fredao;

final class Http {
    public const REQ_METHOD = "REQUEST_METHOD";

    public const POST = "POST";
    public const GET = "GET";
    public const PUT = "PUT";
    public const DELETE = "DELETE";

    public static function checkMethod(string $targetMethod): bool {
        return false;
    }

    public static function build_http_method() {
        // TODO
    }
}