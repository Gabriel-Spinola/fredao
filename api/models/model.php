<?php declare(strict_types=1);
namespace Model;

interface Model
{
    public function get_by_id(int $id): ?Self;
}