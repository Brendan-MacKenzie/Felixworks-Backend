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
            return response()->json([
                'status' => 'fail',
                'message' => __('exceptions.validation'),
                'issue' => $validator->failed(),
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $media = $this->mediaService->store($request->only([
                'media',
                'type',
            ]));
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $media,
        ], 201);
    }

    public function show(Media $media)
    {
        try {
            $file = $this->mediaService->getMediaFile($media->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return $file;
    }

    public function base64(Media $media)
    {
        try {
            $data = $this->mediaService->getMediaFile($media->id, true);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'media' => $media,
            'data' => $data,
        ], 201);
    }

    public function destroy(Media $media)
    {
        try {
            $media = $this->mediaService->delete($media->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Media item is removed.',
        ], 201);
    }
}
