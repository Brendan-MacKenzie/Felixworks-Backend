<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Services\OfficeService;
use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{
    private $officeService;

    public function __construct(OfficeService $officeService)
    {
        $this->officeService = $officeService;
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
                'address_id',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($office);
    }

    public function update(Request $request, Office $office)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'agency_id' => 'integer|exists:agencies,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'address_id' => 'integer|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $office = $this->officeService->update($request->only([
                'agency_id',
                'name',
                'description',
                'website',
                'phone',
                'address_id',
            ]), $office);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($office);
    }

    public function destroy(Office $office)
    {
        try {
            $this->officeService->delete($office);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Office removed successfully');
    }
}
