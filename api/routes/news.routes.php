<?php
namespace Fredao\Router;
use Fredao\StatusCode;
use Model\NewsModel;

require_once __DIR__ . "/../models/news.model.php";
require_once __DIR__ . "/../http.php";

use Fredao\Http;

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
    
}

function handle_put(NewsModel $model, array $url_array): void
{  

}

function handle_delete(NewsModel $model, array $url_array): void
{  

}