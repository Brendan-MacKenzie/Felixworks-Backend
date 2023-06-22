<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Pool;
use Illuminate\Http\Request;
use App\Services\PoolService;
use Illuminate\Support\Facades\Validator;

class PoolController extends Controller
{
    private $poolService;

    public function __construct(PoolService $poolService)
    {
        $this->poolService = $poolService;
    }

    public function show(Pool $pool)
    {
        try {
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
            'branch_id' => 'required|integer|exists:branches,id',
            'employees' => 'array',
            'employees.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $pool = $this->poolService->store($request->only([
                'name',
                'branch_id',
                'employees',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pool);
    }

    public function update(Request $request, Pool $pool)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'branch_id' => 'integer|exists:branches,id',
            'employees' => 'array',
            'employees.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $pool = $this->poolService->update($request->only([
                'name',
                'branch_id',
                'employees',
            ]), $pool);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($pool);
    }
}
