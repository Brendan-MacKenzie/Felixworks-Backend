<?php

namespace App\Services;

use Exception;
use App\Models\Media;
use App\Enums\MediaType;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

class MediaService extends Service
{
    public function store(array $data)
    {
        $media = $data['media'];
        $image = \Intervention\Image\ImageManagerStatic::make($media);

        $image->resize(5000, 5000, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $originalFilename = $this->slugify(pathinfo($media->getClientOriginalName(), PATHINFO_FILENAME));
        $originalFileExtention = $media->getClientOriginalExtension();
        $filename = time().'--'.sha1(base64_encode(random_bytes(10)).$originalFilename).'.'.$originalFileExtention;

        $fullpath = $this->getFullPath($data['type'], $filename);

        if (!$image->save($fullpath)) {
            throw new Exception('Could not store image.', 500);
        }

        $mediaObject = Media::create([
            'name' => $filename,
            'type' => $data['type'],
        ]);

        return $mediaObject;
    }

    public function update(array $data, mixed $media)
    {
    }

    public function delete(mixed $media)
    {
        $fullpath = $this->getFullPath($media->type, $media->name, false);
        if (!Storage::disk('media')->delete($fullpath)) {
            throw new Exception('Could not delete image item.', 500);
        }

        $media->delete();
    }

    public function get(mixed $media)
    {
        return $media;
    }

    public function getMediaFile(Media $media, bool $base64 = false)
    {
        $media = $this->get($id);
        $fullpath = $this->getFullPath($media->type, $media->name, false);

        if (!Storage::disk('media')->exists($fullpath)) {
            throw new Exception('Could not find image.', 404);
        }

        if ($base64) {
            $mediaFile = ImageManagerStatic::make($this->getFullPath($media->type, $media->name));

            return $mediaFile->encode('data-url')->encoded;
        }

        return Storage::disk('media')->download($fullpath);
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    private static function slugify($string, $replace = [], $delimiter = '-')
    {
        if (!extension_loaded('iconv')) {
            throw new Exception('iconv module not loaded');
        }

        // Save the old locale and set the new locale to UTF-8
        $oldLocale = setlocale(LC_ALL, '0');

        setlocale(LC_ALL, 'en_US.UTF-8');

        $clean = iconv('UTF-8', 'ISO-8859-1', $string);

        if (!empty($replace)) {
            $clean = str_replace((array) $replace, ' ', $clean);
        }

        $clean = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace('/[\/_|+ -]+/', $delimiter, $clean);
        $clean = preg_replace('~[^\pL\d]+~u', '-', $clean);
        $clean = preg_replace('~[^-\w]+~', '', $clean);
        $clean = preg_replace('~-+~', '-', $clean);
        $clean = trim($clean, $delimiter);

        // Revert back to the old locale
        setlocale(LC_ALL, $oldLocale);

        return $clean;
    }

    private function getFullPath(int $type, string $filename, bool $fromRoot = true)
    {
        switch($type) {
            case MediaType::Avatar:
                $fullpath = 'avatars/'.$filename;
                break;
            case MediaType::Logo:
                $fullpath = 'logos/'.$filename;
                break;
            default:
                $fullpath = 'default/'.$filename;
                break;
        }

        if ($fromRoot) {
            $fullpath = storage_path('app/'.$fullpath);
        }

        return $fullpath;
    }
}
