<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\CommitmentService;
use Illuminate\Support\Facades\Validator;

class CommitmentController extends Controller
{
    private $commitmentService;

    public function __construct(CommitmentService $commitmentService)
    {
        $this->commitmentService = $commitmentService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'posting_id' => 'required|integer|exists:postings,id',
            'agency_id' => 'required|integer|exists:agencies,id',
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $commitment = $this->commitmentService->store($request->only([
                'posting_id',
                'agency_id',
                'amount',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($commitment);
    }
}
