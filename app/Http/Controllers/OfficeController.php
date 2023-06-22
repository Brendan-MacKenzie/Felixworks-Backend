<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Services\OfficeService;
use App\Services\AddressService;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{
    private $officeService;
    private $addressService;

    public function __construct(OfficeService $officeService, AddressService $addressService)
    {
        $this->officeService = $officeService;
        $this->addressService = $addressService;
    }

    //store office
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|integer|exists:agencies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $office = $this->officeService->store($request->only([
                'agency_id',
                'name',
                'description',
                'website',
                'phone',
            ]));

            $this->addressService->linkModel($request->input('address_id'), $office);

            $office = $this->officeService->get($office);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($office);
    }

    public function update(Request $request, Office $office)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $office = $this->officeService->update($request->only([
                'name',
                'description',
                'website',
                'phone',
            ]), $office);

            $office = $this->officeService->get($office);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($office);
    }

    public function destroy(Office $office)
    {
        try {
            $this->addressService->unlinkModel($office->address);
            $this->officeService->delete($office);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Office removed successfully');
    }
}
