<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Media;
use App\Enums\MediaType;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\MediaService;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    use AccessManager;

    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media' => 'required|image|max:5000',
            'type' => 'required|integer|in:'.implode(',', MediaType::getValues()),
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $media = $this->mediaService->store($request->only([
                'media',
                'type',
            ]));
        } catch (Exception $exception) {
            if ($media) {
                $this->mediaService->delete($media);
            }

            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($media);
    }

    public function show(Media $media)
    {
        try {
            $this->canAccess($media);
            $file = $this->mediaService->getMediaFile($media);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $file;
    }

    public function base64(Media $media)
    {
        try {
            $this->canAccess($media);
            $data = $this->mediaService->getMediaFile($media, true);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($media, $data);
    }

    public function destroy(Media $media)
    {
        try {
            $this->canAccess($media);
            $media = $this->mediaService->delete($media);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Media item is removed.');
    }
}
