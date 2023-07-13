<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Agency;
use App\Models\Media;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\AgencyService;
use App\Services\DeclarationService;
use Illuminate\Support\Facades\Validator;

class AgencyController extends Controller
{
    use AccessManager;

    private $agencyService;
    private $declarationService;

    public function __construct(AgencyService $agencyService, DeclarationService $declarationService)
    {
        $this->agencyService = $agencyService;
        $this->declarationService = $declarationService;
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
            'email' => 'required|email|string|max:255',
            'base_rate' => 'required|integer',
            'brand_color' => 'required|string|max:255',
            'ip_address' => 'required_with:webhook|string',
            'webhook' => 'required_with:ip_address|string',
            'logo_uuid' => 'nullable|uuid',
            'regions' => 'required|array|min:1',
            'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin']);
            
            $agency = $this->agencyService->store($request->only([
                'name',
                'full_name',
                'email',
                'base_rate',
                'brand_color',
                'ip_address',
                'webhook',
                'logo_uuid',
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
            'email' => 'email|string|max:255',
            'base_rate' => 'integer',
            'brand_color' => 'string|max:255',
            'ip_address' => 'string',
            'webhook' => 'string',
            'logo_uuid' => 'nullable|uuid',

            'regions' => 'array|min:1',
            'regions.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($agency, true);

            if ($request->has('logo_uuid')) {
                $media = Media::findOrFail($request->input('logo_uuid'));
                $this->canAccess($media);
            }
            
            $agency = $this->agencyService->update($request->only([
                'name',
                'full_name',
                'email',
                'base_rate',
                'brand_color',
                'ip_address',
                'webhook',
                'logo_uuid',
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
            $this->canAccess($agency);
            $agency = $this->agencyService->get($agency);

            $agency->load('regions');
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($agency);
    }

    public function listDeclarations(Request $request, Agency $agency)
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
            $this->canAccess($agency);
            $declarations = $this->declarationService->listAgencyDeclarations($perPage, $search, $agency);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($declarations);
    }
}
