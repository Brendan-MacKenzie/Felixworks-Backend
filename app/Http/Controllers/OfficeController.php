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
                'address_id',
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
            'agency_id' => 'integer|exists:agencies,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'address_id' => 'integer|exists:addresses,id',
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
                'address_id',
            ]), $office);
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
            $this->officeService->delete($office);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Office removed successfully',
        ], 200);
    }
}
