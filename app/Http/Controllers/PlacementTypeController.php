<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\PlacementType;
use App\Services\PlacementTypeService;
use Illuminate\Support\Facades\Validator;

class PlacementTypeController extends Controller
{
    private $placementTypeService;

    public function __construct(PlacementTypeService $placementTypeService)
    {
        $this->placementTypeService = $placementTypeService;
    }

    public function getPlacementTypesByLocation(Request $request, $location)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $search = $request->input('search', null);

        try {
            $placementTypes = $this->placementTypeService->listByLocation($location, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placementTypes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location_id' => 'required|integer|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placementType = $this->placementTypeService->store($request->only([
                'name',
                'location_id',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placementType);
    }

    public function destroy(PlacementType $placementType)
    {
        try {
            $this->placementTypeService->delete($placementType);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Placement Type removed successfully');
    }
}
