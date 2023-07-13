<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Exception;
use App\Models\Workplace;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\WorkplaceService;
use Illuminate\Support\Facades\Validator;

class WorkplaceController extends Controller
{
    use AccessManager;

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

        $search = $request->input('search', null);

        try {
            $this->rolesCanAccess(['admin']);
            $workplaces = $this->workplaceService->list(25, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($workplaces);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $address = Address::findOrFail($request->input('address_id'));
            $this->canAccess($address);

            $workplace = $this->workplaceService->store($request->only([
                'name',
                'address_id',
            ]));

            $workplace = $this->workplaceService->get($workplace);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($workplace);
    }

    public function update(Request $request, Workplace $workplace)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($workplace);

            $workplace = $this->workplaceService->update($request->only([
                'name',
            ]), $workplace);

            $workplace = $this->workplaceService->get($workplace);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($workplace);
    }

    public function destroy(Workplace $workplace)
    {
        try {
            $this->canAccess($workplace);
            
            $this->workplaceService->delete($workplace);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Workplace removed successfully');
    }
}
