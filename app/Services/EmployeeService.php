<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        return Employee::create($data);
    }

    public function update(array $data, mixed $employee)
    {
    }

    public function delete(mixed $employee)
    {
    }

    public function get(mixed $employee)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
