<?php declare(strict_types=1);
namespace Fredao\Router\News;

require_once __DIR__ . "/../http.php";
require_once __DIR__ . "/../models/news.model.php";

use Fredao\Http;
use Fredao\StatusCode;
use Model\NewsModel;
use Model\NewsModelFields;

function news_routes(string $method, NewsModel $model, array $url_array): void
{
    match ($method) {
        Http::GET => handle_get($model, $url_array),
        Http::POST => handle_post($model, $url_array),
        Http::PUT => handle_put($model, $url_array),
        Http::DELETE => handle_delete($model, $url_array),
    };
}

// REVIEW - PAGINATION
function handle_get(NewsModel $model, array $url_array): void
{  
    $data = $model->get_all();
    if ($data and empty($data)) {
        Http::not_found();

        return;
    }

    Http::build_response(StatusCode::OK, $data);
}

function handle_post(NewsModel $model, array $url_array): void
{  
    $body = file_get_contents("php://input");
    $decoded = json_decode($body, true);

    if (!validate_insert($decoded, $model)) {
        Http::build_response(StatusCode::UNPROCESSABLE_ENTITY);

        return;
    }

    if (!$model->insert()) {
        Http::build_response(StatusCode::INTERNAL_SERVER_ERROR, "Failed to insert news");

        return;
    }

    Http::build_response(StatusCode::OK, $decoded);
}

function handle_put(NewsModel $model, array $url_array): void
{  

}

function handle_delete(NewsModel $model, array $url_array): void
{  

}

function validate_insert(?array $decoded, NewsModel &$result): bool
{
    if (!$decoded or empty($decoded)) {
        return false;
    }

    $expected_keys = NewsModelFields::REQUIRED_FIELDS;

    // NOTE - check if array match all expected keys
    if (count(array_intersect_key(array_flip($expected_keys), $decoded)) === count($expected_keys)) {
        $result->title = $decoded[NewsModelFields::TITLE];
        $result->description = $decoded[NewsModelFields::DESCRIPTION];
        $result->content = $decoded[NewsModelFields::CONTENT];
        $result->image = $decoded[NewsModelFields::IMAGE];
        $result->creator_id = $decoded[NewsModelFields::CREATOR_ID];

        return true;
    }

    return false;
}