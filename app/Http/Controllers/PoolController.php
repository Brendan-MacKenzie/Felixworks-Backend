<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Exception;
use App\Models\Pool;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\PoolService;
use Illuminate\Support\Facades\Validator;

class PoolController extends Controller
{
    use AccessManager;

    private $poolService;

    public function __construct(PoolService $poolService)
    {
        $this->poolService = $poolService;
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
            $this->rolesCanAccess(['admin']);
            $pools = $this->poolService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pools);
    }

    public function show(Pool $pool)
    {
        try {
            $this->canAccess($pool);

            $pool = $this->poolService->get($pool);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pool);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location_id' => 'required|integer',
            'employees' => 'array',
            'employees.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $location = Location::findOrFail($request->input('location_id'));
            $this->canAccess($location);

            $pool = $this->poolService->store($request->only([
                'name',
                'location_id',
                'employees',
            ]));

            $pool = $this->poolService->get($pool);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pool);
    }

    public function update(Request $request, Pool $pool)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'employees' => 'array',
            'employees.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($pool);

            $pool = $this->poolService->update($request->only([
                'name',
                'employees',
            ]), $pool);

            $pool = $this->poolService->get($pool);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pool);
    }

    public function destroy(Pool $pool)
    {
        try {
            $this->canAccess($pool);
            
            $this->poolService->delete($pool);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Pool removed successfully');
    }
}
