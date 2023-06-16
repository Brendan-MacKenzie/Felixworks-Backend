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
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $perPage = $request->input('per_page', 25);
        $search = $request->input('search', null);

        try {
            $workplaces = $this->workplaceService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($workplaces);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $workplace = $this->workplaceService->store($request->only([
                'name',
                'address_id',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($workplace);
    }

    public function update(Request $request, Workplace $workplace)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'address_id' => 'integer|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $workplace = $this->workplaceService->update($request->only([
                'name',
                'address_id',
            ]), $workplace);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

       return $this->successResponse($workplace);
    }

    public function destroy(Workplace $workplace)
    {
        try {
            $this->workplaceService->delete($workplace);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Workplace removed successfully');
    }
}
