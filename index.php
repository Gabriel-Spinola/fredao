<?php
namespace Fredao;

require './routes.php';

// gabriel.fredaugusto.com.br
const DATABASE_HOST = "https://gabriel.fredaugusto.com.br/";
const DATABASE_PORT = "";
const DATABASE_USER = "u168309973_gabriel";
const DATABASE_PASSWORD = "RBO098oP";

enum Position: string {
    case User = 'USER';
    case Admin = 'ADMIN';
}

Router\allow_cors();
Router\routes();
