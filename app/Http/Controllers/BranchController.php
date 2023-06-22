<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Services\BranchService;
use App\Services\AddressService;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    private $branchService;
    private $addressService;

    public function __construct(BranchService $branchService, AddressService $addressService)
    {
        $this->branchService = $branchService;
        $this->addressService = $addressService;
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
            $branches = $this->branchService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($branches);
    }

    public function show(Branch $branch)
    {
        try {
            $branch = $this->branchService->get($branch);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($branch);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dresscode' => 'required|string|max:255',
            'briefing' => 'required|string|max:255',
            'address_id' => 'required|integer|exists:addresses,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $branch = $this->branchService->store($request->only([
                'name',
                'dresscode',
                'briefing',
                'regions',
            ]));

            $this->addressService->linkModel($request->input('address_id'), $branch);

            $branch = $this->branchService->get($branch);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($branch);
    }

    public function update(Request $request, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'dresscode' => 'string|max:255',
            'briefing' => 'string|max:255',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $branch = $this->branchService->update($request->only([
                'name',
                'dresscode',
                'briefing',
                'regions',
            ]), $branch);

            $branch = $this->branchService->get($branch);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($branch);
    }
}
