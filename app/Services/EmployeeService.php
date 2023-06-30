<?php

namespace App\Services;

use Exception;
use App\Models\Media;
use App\Models\Agency;
use App\Enums\MediaType;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeService extends Service
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        if (
            key_exists('avatar_uuid', $data) &&
            !is_null($data['avatar_uuid'])
        ) {
            $media = Media::findOrFail($data['avatar_uuid']);
            if ($media->type !== MediaType::Avatar) {
                throw new Exception('Image is not an avatar.', 403);
            }
        }

        if (key_exists('external_id', $data)) {
            $agency = Agency::findOrFail($data['agency_id']);
            $employee = $agency->employees()->where('external_id', $data['external_id'])->first();

            if ($employee) {
                return $this->update($data, $employee);
            }
        }

        return Employee::create($data);
    }

    public function update(array $data, mixed $employee)
    {
        if (
            key_exists('avatar_uuid', $data)
        ) {
            $toDelete = (is_null($data['avatar_uuid'])) ? true : false;
            if (!is_null($data['avatar_uuid'])) {
                $media = Media::findOrFail($data['avatar_uuid']);
                if ($media->type !== MediaType::Avatar) {
                    throw new Exception('Image is not a logo.', 403);
                }

                if ($employee->avatar_uuid && $employee->avatar_uuid !== $media->id) {
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
