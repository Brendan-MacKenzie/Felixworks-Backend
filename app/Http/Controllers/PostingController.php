<?php

namespace App\Http\Controllers;

use Exception;
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
           'posting_end_date' => 'nullable|date',
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
}
