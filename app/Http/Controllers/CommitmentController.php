<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Exception;
use App\Models\Commitment;
use App\Models\Posting;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\CommitmentService;
use Illuminate\Support\Facades\Validator;

class CommitmentController extends Controller
{
    use AccessManager;

    private $commitmentService;

    public function __construct(CommitmentService $commitmentService)
    {
        $this->commitmentService = $commitmentService;
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
            $this->rolesCanAccess(['admin']);
            $commitments = $this->commitmentService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($commitments);
    }

    public function show(Commitment $commitment)
    {
        try {
            $this->canAccess($commitment);
            $commitment = $this->commitmentService->get($commitment);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($commitment);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'posting_id' => 'required|integer',
            'agency_id' => 'required|integer',
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $agency = Agency::findOrFail($request->input('agency_id'));
            $posting = Posting::findOrFail($request->input('posting_id'));

            $this->rolesCanAccess(['admin', 'agent']);
            $this->canAccess([$agency, $posting]);

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

    public function update(Request $request, Commitment $commitment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($commitment);
            $commitment = $this->commitmentService->update($request->only([
                'amount',
            ]), $commitment, );
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($commitment);
    }

    public function destroy(Commitment $commitment)
    {
        try {
            $this->canAccess($commitment);
            $this->commitmentService->delete($commitment);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Commitment deleted successfully.');
    }
}
