<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Media;
use App\Enums\MediaType;
use Illuminate\Http\Request;
use App\Services\MediaService;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
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
            $this->failedValidationResponse($validator);
        }

        try {
            $media = $this->mediaService->store($request->only([
                'media',
                'type',
            ]));
        } catch (Exception $exception) {
            $this->failedExceptionResponse($exception);
        }

        $this->successResponse($media);
    }

    public function show(Media $media)
    {
        try {
            $file = $this->mediaService->getMediaFile($media);
        } catch (Exception $exception) {
            $this->failedExceptionResponse($exception);
        }

        return $file;
    }

    public function base64(Media $media)
    {
        try {
            $data = $this->mediaService->getMediaFile($media, true);
        } catch (Exception $exception) {
            $this->failedExceptionResponse($exception);
        }

        $this->successResponse($media, $data);
    }

    public function destroy(Media $media)
    {
        try {
            $media = $this->mediaService->delete($media);
        } catch (Exception $exception) {
            $this->failedExceptionResponse($exception);
        }

        $this->messageResponse('Media item is removed.');
    }
}
