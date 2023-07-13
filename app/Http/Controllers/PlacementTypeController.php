<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Exception;
use Illuminate\Http\Request;
use App\Models\PlacementType;
use App\Services\Access\AccessManager;
use App\Services\PlacementTypeService;
use Illuminate\Support\Facades\Validator;

class PlacementTypeController extends Controller
{
    use AccessManager;

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
            $this->canAccess($location);
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
            'location_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $location = Location::findOrFail($request->input('location_id'));
            $this->canAccess($location);

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
            $this->canAccess($placementType);
            $this->placementTypeService->delete($placementType);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Placement Type removed successfully');
    }
}
