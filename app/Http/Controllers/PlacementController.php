<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Placement;
use App\Models\PlacementType;
use App\Models\Posting;
use App\Models\Workplace;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\PlacementService;
use Illuminate\Support\Facades\Validator;

class PlacementController extends Controller
{
    use AccessManager;

    private $placementService;

    public function __construct(PlacementService $placementService)
    {
        $this->placementService = $placementService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'posting_id' => 'required|integer',
            'workplace_id' => 'nullable|integer',
            'placement_type_id' => 'required|integer',
            'report_at' => 'required|date|before_or_equal:start_at',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin', 'client']);
            $posting = Posting::findOrFail($request->input('posting_id'));
            $placementType = PlacementType::findOrFail($request->input('placement_type_id'));
            $this->canAccess([$posting, $placementType]);

            if ($request->has('workplace_id')) {
                $workplace = Workplace::findOrFail($request->input('workplace_id'));
                $this->canAccess($workplace);
            }

            $placement = $this->placementService->store($request->only([
                'posting_id',
                'workplace_id',
                'placement_type_id',
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
            'report_at' => 'date|before_or_equal:start_at',
            'start_at' => 'date',
            'end_at' => 'date|after:start_at',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin', 'client']);
            $this->canAccess($placement);

            if ($request->has('workplace_id') && $request->input('workplace_id')) {
                $workplace = Workplace::findOrFail($request->input('workplace_id'));
                $this->canAccess($workplace);
            }

            if ($request->has('placement_type_id')) {
                $placementType = PlacementType::findOrFail($request->input('placement_type_id'));
                $this->canAccess($placementType);
            }

            $placement = $this->placementService->update($request->only([
                'workplace_id',
                'placement_type_id',
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
