<?php

namespace App\Services;

abstract class Service
{
    abstract public function store(array $data);

    abstract public function update(array $data, int $id);

    abstract public function delete(int $id);

    abstract public function get(int $id, bool $withArchived = false);

    abstract public function list(int $perPage = 25, string $archiveStatus = 'active', string $query = null);
}
