<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $search = $request->input('search', null);
        $perPage = $request->input('per_page', 25);

        try {
            $employees = $this->employeeService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employees);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'integer|exists:agencies,id',
            'external_id' => 'string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'date',
            'avatar_id' => 'integer|exists:media,id',
            'drivers_license' => 'boolean',
            'car' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $employee = $this->employeeService->store($request->only([
                'agency_id',
                'external_id',
                'first_name',
                'last_name',
                'date_of_birth',
                'avatar_id',
                'drivers_license',
                'car',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'integer|exists:agencies,id',
            'external_id' => 'string|max:255',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'date_of_birth' => 'date',
            'avatar_id' => 'integer|exists:media,id',
            'drivers_license' => 'boolean',
            'car' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $employee = $this->employeeService->update($request->only([
                'agency_id',
                'external_id',
                'first_name',
                'last_name',
                'date_of_birth',
                'avatar_id',
                'drivers_license',
                'car',
            ]), $employee);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }
}
