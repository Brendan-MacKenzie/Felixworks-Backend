<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Declaration;
use App\Models\Placement;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\DeclarationService;
use Illuminate\Support\Facades\Validator;

class DeclarationController extends Controller
{
    use AccessManager;

    private $declarationService;

    public function __construct(DeclarationService $declarationService)
    {
        $this->declarationService = $declarationService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'total' => 'required|numeric',
            'placement_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placement = Placement::findOrFail($request->input('placement_id'));
            $this->rolesCanAccess(['admin', 'agent']);
            $this->canAccess($placement);

            $declaration = $this->declarationService->store($request->only([
                'title',
                'total',
                'placement_id',
            ]));

            $declaration = $this->declarationService->get($declaration);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($declaration);
    }

    public function update(Request $request, Declaration $declaration)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'total' => 'numeric',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($declaration);

            $declaration = $this->declarationService->update($request->only([
                'title',
                'total',
            ]), $declaration);

            $declaration = $this->declarationService->get($declaration);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($declaration);
    }

    public function destroy(Declaration $declaration)
    {
        try {
            $this->canAccess($declaration);
            $this->declarationService->delete($declaration);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Declaration removed successfully.');
    }
}
