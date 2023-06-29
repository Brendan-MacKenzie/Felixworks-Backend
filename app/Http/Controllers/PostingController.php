<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Posting;
use App\Enums\RepeatType;
use Illuminate\Http\Request;
use App\Services\PostingService;
use Illuminate\Support\Facades\Validator;

class PostingController extends Controller
{
    private $postingService;

    public function __construct(PostingService $postingService)
    {
        $this->postingService = $postingService;
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
            $postings = $this->postingService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($postings);
    }

    public function indexCancelled(Request $request)
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
            $postings = $this->postingService->listCancelled($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($postings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255',
           'address_id' => 'required|integer|exists:addresses,id',
           'dresscode' => 'string|max:255',
           'briefing' => 'string|max:255',
           'information' => 'string|max:255',
           'cancelled_at' => 'date',
           'agencies' => 'required|array|min:1',
           'agencies.*' => 'integer|exists:agencies,id',
           'regions' => 'required|array|min:1',
           'regions.*' => 'integer|exists:regions,id',
           'repeat_type' => 'integer|in:'.implode(',', RepeatType::getValues()),
           'posting_start_date' => 'required|date',
           'posting_end_date' => 'nullable|date|after_or_equal:posting_start_date',
           'placements' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $posting = $this->postingService->store($request->only([
                'name',
                'address_id',
                'dresscode',
                'briefing',
                'information',
                'cancelled_at',
                'agencies',
                'regions',
                'repeat_type',
                'posting_start_date',
                'posting_end_date',
                'placements',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function update(Request $request, Posting $posting)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'string|max:255',
           'address_id' => 'integer|exists:addresses,id',
           'dresscode' => 'string|max:255',
           'briefing' => 'string|max:255',
           'information' => 'string|max:255',
           'cancelled_at' => 'date',
           'agencies' => 'array|min:1',
           'agencies.*' => 'integer|exists:agencies,id',
           'regions' => 'array|min:1',
           'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $posting = $this->postingService->update($request->only([
                'name',
                'address_id',
                'dresscode',
                'briefing',
                'information',
                'cancelled_at',
                'agencies',
                'regions',
            ]), $posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function show(Posting $posting)
    {
        try {
            $posting = $this->postingService->get($posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }
}
