<?php

namespace App\Services;

use Exception;
use App\Models\Media;
use App\Enums\MediaType;
use App\Models\Employee;

class EmployeeService extends Service
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->user()->id;

        if (
            key_exists('avatar_id', $data) &&
            !is_null($data['avatar_id'])
        ) {
            $media = Media::findOrFail($data['avatar_id']);
            if ($media->type !== MediaType::Avatar) {
                throw new Exception('Image is not an avatar.', 403);
            }
        }

        return Employee::create($data);
    }

    public function update(array $data, mixed $employee)
    {
        if (
            key_exists('avatar_id', $data)
        ) {
            $toDelete = (is_null($data['avatar_id'])) ? true : false;
            if (!is_null($data['avatar_id'])) {
                $media = Media::findOrFail($data['avatar_id']);
                if ($media->type !== MediaType::Avatar) {
                    throw new Exception('Image is not a logo.', 403);
                }

                if ($employee->avatar_id && $employee->avatar_id !== $media->id) {
                    $toDelete = true;
                }
            }

            if ($toDelete && $employee->avatar) {
                $this->mediaService->delete($employee->avatar);
            }
        }

        // Update employee attributes
        $employee->update($data);

        // Return the updated employee with the pools
        return $employee;
    }

    public function delete(mixed $employee)
    {
        $employee->delete();
    }

    public function get(mixed $employee)
    {
        $employee->load([
            'branches',
            'agency',
            'pools',
            'avatar',
        ])
        ->loadCount('placements');

        return $employee;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Employee::when($query, function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%");
        })
        ->withCount('placements')
        ->paginate($perPage);
    }
}
