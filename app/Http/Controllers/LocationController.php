<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Exception;
use App\Models\Location;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\AddressService;
use App\Services\LocationService;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    use AccessManager;

    private $locationService;
    private $addressService;

    public function __construct(LocationService $locationService, AddressService $addressService)
    {
        $this->locationService = $locationService;
        $this->addressService = $addressService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $perPage = $request->input('per_page', 25);
        $search = $request->input('search', null);

        try {
            $this->rolesCanAccess(['admin']);
            $locations = $this->locationService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($locations);
    }

    public function show(Location $location)
    {
        try {
            $this->canAccess($location);
            $location = $this->locationService->get($location);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($location);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dresscode' => 'required|string|max:255',
            'briefing' => 'required|string|max:255',
            'address_id' => 'required|integer|exists:addresses,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin', 'client']);
            $location = $this->locationService->store($request->only([
                'name',
                'dresscode',
                'briefing',
                'regions',
            ]));

            $this->addressService->linkModel($request->input('address_id'), $location);

            $location = $this->locationService->get($location);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($location);
    }

    public function update(Request $request, Location $location)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'dresscode' => 'string|max:255',
            'briefing' => 'string|max:255',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($location);
            $location = $this->locationService->update($request->only([
                'name',
                'dresscode',
                'briefing',
                'regions',
            ]), $location);

            $location = $this->locationService->get($location);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($location);
    }
}
