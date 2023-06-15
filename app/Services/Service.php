<?php

namespace App\Services;

abstract class Service
{
    abstract public function store(array $data);

    abstract public function update(array $data, mixed $item);

    abstract public function delete(mixed $item);

    abstract public function get(mixed $item);

    abstract public function list(int $perPage = 25, string $query = null);
}
