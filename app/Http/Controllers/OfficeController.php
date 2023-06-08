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
            'street_name' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
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
            $office = $this->officeService->store($request->only([
                'agency_id',
                'name',
                'description',
                'website',
                'phone',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
            ]));
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $office,
        ], 201);
    }

    public function update(Request $request, Office $office)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|integer|exists:agencies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'street_name' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
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
            $office = $this->officeService->update($request->only([
                'agency_id',
                'name',
                'description',
                'website',
                'phone',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
            ]), $office->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $office,
        ], 200);
    }

    public function destroy(Office $office)
    {
        try {
            $this->officeService->delete($office->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Óffice removed successfully',
        ], 200);
    }
}
