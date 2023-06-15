<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\RegionService;
use Illuminate\Support\Facades\Validator;

class RegionController extends Controller
{
    private $regionService;

    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            $this->failedValidationResponse($validator);
        }

        $perPage = $request->input('per_page', 25);
        $search = $request->input('search', null);

        try {
            $regions = $this->regionService->list($perPage, $search);
        } catch (Exception $exception) {
            $this->failedExceptionResponse($exception);
        }

        return response()->json([
            'status' => 'success',
            'data' => $regions,
        ], 200);
    }
}
