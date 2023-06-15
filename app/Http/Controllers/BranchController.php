<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Services\BranchService;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    private $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => __('exceptions.validation'),
                'issue' => $validator->failed(),
                'errors' => $validator->errors(),
            ], 400);
        }

        $search = $request->input('search', null);

        try {
            $branches = $this->branchService->list(0, $search);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dresscode' => 'required|string|max:255',
            'briefing' => 'required|string|max:255',
            'client_id' => 'required|integer|exists:clients,id',
            'address_id' => 'required|integer|exists:addresses,id',
            'regions' => 'required|array|min:1', // Validation for regions as an array with minimum 1 element
            'regions.*' => 'integer|exists:regions,id', // Validation for each region ID in the array
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
            $branch = $this->branchService->store($request->only([
                'name',
                'dresscode',
                'briefing',
                'client_id',
                'address_id',
                'regions',
            ]));
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $branch,
        ], 201);
    }

    public function update(Request $request, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'dresscode' => 'string|max:255',
            'briefing' => 'string|max:255',
            'client_id' => 'integer|exists:clients,id',
            'address_id' => 'integer|exists:addresses,id',
            'regions' => 'required|array|min:1', // Validation for regions as an array with minimum 1 element
            'regions.*' => 'integer|exists:regions,id', // Validation for each region ID in the array
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
            $branch = $this->branchService->update($request->only([
                'name',
                'dresscode',
                'briefing',
                'client_id',
                'address_id',
                'regions',
            ]), $branch->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $branch,
        ], 200);
    }
}
