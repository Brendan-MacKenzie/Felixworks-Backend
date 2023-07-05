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

    public function show(Employee $employee)
    {
        try {
            $employee = $this->employeeService->get($employee);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|integer|exists:agencies,id',
            'external_id' => 'string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'avatar_uuid' => 'uuid',
            'drivers_license' => 'required|boolean',
            'car' => 'required|boolean',
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
                'avatar_uuid',
                'drivers_license',
                'car',
            ]));

            $employee = $this->employeeService->get($employee);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'date_of_birth' => 'date',
            'avatar_uuid' => 'uuid',
            'drivers_license' => 'boolean',
            'car' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $employee = $this->employeeService->update($request->only([
                'first_name',
                'last_name',
                'date_of_birth',
                'avatar_uuid',
                'drivers_license',
                'car',
            ]), $employee);

            $employee = $this->employeeService->get($employee);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }

    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->delete($employee);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Employee removed successfully');
    }
}
