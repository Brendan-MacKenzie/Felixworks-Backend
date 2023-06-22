<?php

namespace App\Services;

use Exception;
use App\Models\Media;
use App\Models\Agency;
use App\Enums\MediaType;
use Illuminate\Support\Str;

class AgencyService extends Service
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
            key_exists('logo_id', $data) &&
            !is_null($data['logo_id'])
        ) {
            $media = Media::findOrFail($data['logo_id']);
            if ($media->type !== MediaType::Logo) {
                throw new Exception('Image is not a logo.', 403);
            }
        }

        if (
            key_exists('ip_address', $data) &&
            key_exists('webhook', $data)
        ) {
            $data['api_key'] = Str::random(32);
            $data['webhook_key'] = Str::random(32);
        }

        $agency = Agency::create($data);

        if (key_exists('regions', $data)) {
            $agency->regions()->sync($data['regions']);
        }

        if (
            key_exists('ip_address', $data) &&
            key_exists('webhook', $data)
        ) {
            $agency->makeVisible(['api_key', 'webhook_key']);
        }

        $agency->load('regions');

        return $agency;
    }

    public function update(array $data, mixed $agency)
    {
        if (
            key_exists('logo_id', $data)
        ) {
            $toDelete = (is_null($data['logo_id'])) ? true : false;
            if (!is_null($data['logo_id'])) {
                $media = Media::findOrFail($data['logo_id']);
                if ($media->type !== MediaType::Logo) {
                    throw new Exception('Image is not a logo.', 403);
                }

                if ($agency->logo_id && $agency->logo_id !== $media->id) {
                    $toDelete = true;
                }
            }

            if ($toDelete) {
                $this->mediaService->delete($agency->logo);
            }
        }

        $agency->update($data);

        if (key_exists('regions', $data)) {
            $agency->regions()->sync($data['regions']);
        }

        $agency->refresh();
        $agency->load('regions');

        return $agency;
    }

    public function delete(mixed $agency)
    {
    }

    public function get(mixed $agency)
    {
        $agency->load([
            'offices',
            'commitments',
            'users',
            'employees',
            'regions',
        ]);

        return $agency;
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return Agency::when(!is_null($query), function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })->with('regions')->paginate($perPage);
    }
}
