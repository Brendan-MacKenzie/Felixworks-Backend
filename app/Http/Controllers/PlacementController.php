<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Placement;
use Illuminate\Http\Request;
use App\Services\PlacementService;
use Illuminate\Support\Facades\Validator;

class PlacementController extends Controller
{
    private $placementService;

    public function __construct(PlacementService $placementService)
    {
        $this->placementService = $placementService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'posting_id' => 'required|integer|exists:postings,id',
            'workplace_id' => 'required|integer|exists:workplaces,id',
            'placement_type_id' => 'required|integer|exists:placement_types,id',
            'employee_id' => 'integer|exists:employees,id',
            'report_at' => 'required|date|before_or_equal:start_at',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placement = $this->placementService->store($request->only([
                'posting_id',
                'workplace_id',
                'placement_type_id',
                'employee_id',
                'report_at',
                'start_at',
                'end_at',
            ]));

            $placement = $this->placementService->get($placement);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placement);
    }

    public function update(Request $request, Placement $placement)
    {
        $validator = Validator::make($request->all(), [
            'workplace_id' => 'nullable|integer',
            'placement_type_id' => 'integer',
            'employee_id' => 'nullable|integer',
            'report_at' => 'date|before_or_equal:start_at',
            'start_at' => 'date',
            'end_at' => 'date|after:start_at',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placement = $this->placementService->update($request->only([
                'workplace_id',
                'placement_type_id',
                'employee_id',
                'report_at',
                'start_at',
                'end_at',
            ]), $placement);

            $placement = $this->placementService->get($placement);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placement);
    }
}
