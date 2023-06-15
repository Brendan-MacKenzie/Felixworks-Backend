<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Workplace;
use Illuminate\Http\Request;
use App\Services\AgencyService;
use Illuminate\Support\Facades\Validator;

class AgencyController extends Controller
{
    private $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
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
            $workplaces = $this->workplaceService->list(0, $search);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $workplaces,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'brand_color' => 'required|string|max:255',
            'logo_id' => 'nullable|integer',

            'regions' => 'required|array|min:1',
            'regions.*' => 'integer',
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
            $agency = $this->agencyService->store($request->only([
                'name',
                'full_name',
                'brand_color',
                'logo_id',
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
            'data' => $agency,
        ], 201);
    }

    public function update(Request $request, Workplace $workplace)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
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
            $workplace = $this->workplaceService->update($request->only([
                'name',
                'address_id',
            ]), $workplace->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $workplace,
        ], 200);
    }
}
