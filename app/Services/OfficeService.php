<?php

namespace App\Services;

use Exception;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;

class OfficeService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        return Office::create($data);
    }

    public function update(array $data, mixed $office)
    {
        $office->update($data);

        return $office;
    }

    public function delete(mixed $office)
    {
        if ($office->address) {
            throw new Exception("You can't delete an office with a linked address.");
        }

        $office->delete();
    }

    public function get(mixed $office)
    {
        $office->load([
            'address',
            'agency',
            'regions',
            'createdBy',
        ]);

        return $office;
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
