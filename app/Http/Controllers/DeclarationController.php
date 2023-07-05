<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Declaration;
use Illuminate\Http\Request;
use App\Services\DeclarationService;
use Illuminate\Support\Facades\Validator;

class DeclarationController extends Controller
{
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

    public function destroy(Declaration $declaration)
    {
        try {
            $this->declarationService->delete($declaration);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Declaration removed successfully.');
    }
}
