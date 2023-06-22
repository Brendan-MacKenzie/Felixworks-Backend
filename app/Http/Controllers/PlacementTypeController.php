<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\PlacementTypeService;
use Illuminate\Support\Facades\Validator;

class PlacementTypeController extends Controller
{
    private $placementTypeService;

    public function __construct(PlacementTypeService $placementTypeService)
    {
        $this->placementTypeService = $placementTypeService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placementType = $this->placementTypeService->store($request->only([
                'name',
                'branch_id',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placementType);
    }
}