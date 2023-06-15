<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Agency;
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
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $perPage = $request->input('per_page', 25);
        $search = $request->input('search', null);

        try {
            $agencies = $this->agencyService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($agencies);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'brand_color' => 'required|string|max:255',
            'ip_address' => 'required_with:webhook|string',
            'webhook' => 'required_with:ip_address|string',
            'logo_id' => 'nullable|integer',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $agency = $this->agencyService->store($request->only([
                'name',
                'full_name',
                'brand_color',
                'ip_address',
                'webhook',
                'logo_id',
                'regions',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($agency);
    }

    public function update(Request $request, Agency $agency)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'full_name' => 'string|max:255',
            'brand_color' => 'string|max:255',
            'ip_address' => 'string',
            'webhook' => 'string',
            'logo_id' => 'nullable|integer',

            'regions' => 'array|min:1',
            'regions.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $agency = $this->agencyService->update($request->only([
                'name',
                'full_name',
                'brand_color',
                'ip_address',
                'webhook',
                'logo_id',
                'regions',
            ]), $agency);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($agency);
    }

    public function show(Agency $agency)
    {
        try {
            $agency = $this->agencyService->get($agency);

            $agency->load('regions');
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($agency);
    }
}
