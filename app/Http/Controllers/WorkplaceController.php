<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Workplace;
use Illuminate\Http\Request;
use App\Services\WorkplaceService;
use Illuminate\Support\Facades\Validator;

class WorkplaceController extends Controller
{
    private $workplaceService;

    public function __construct(WorkplaceService $workplaceService)
    {
        $this->workplaceService = $workplaceService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 400);
        }

        $search = $request->input('search', null);

        try {
            $workplaces = $this->workplaceService->list(0, '', $search);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => __('exceptions.validation'),
                'issue' => $validator->failed(),
                'errors' => $validator->errors(),
            ], 400);
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
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $workplace = $this->workplaceService->store($request->only([
                'name',
                'client_id',
            ]));
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => __('exceptions.validation'),
                'issue' => $validator->failed(),
                'errors' => $validator->errors(),
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $workplace,
        ], 201);
    }

    public function update(Request $request, Workplace $workplace)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $workplace = $this->workplaceService->update($request->only([
                'name',
                'client_id',
            ]), $workplace->id);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => __('exceptions.validation'),
                'issue' => $validator->failed(),
                'errors' => $validator->errors(),
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $workplace,
        ], 200);
    }
}
