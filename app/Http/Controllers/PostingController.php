<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Posting;
use App\Enums\RepeatType;
use App\Models\Address;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\PostingService;
use App\Services\PlacementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostingController extends Controller
{
    use AccessManager;

    private $postingService;
    private $placementService;

    public function __construct(
        PostingService $postingService,
        PlacementService $placementService
    ) {
        $this->postingService = $postingService;
        $this->placementService = $placementService;
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
        $cancelled = boolval($request->input('cancelled', false));

        try {
            $this->rolesCanAccess(['admin']);

            $postings = $this->postingService->list($perPage, $search, $cancelled);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($postings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255',
           'address_id' => 'required|integer',
           'dresscode' => 'string|max:255',
           'briefing' => 'string|max:255',
           'information' => 'string|max:255',
           'agencies' => 'required|array|min:1',
           'agencies.*' => 'integer|exists:agencies,id',
           'regions' => 'required|array|min:1',
           'regions.*' => 'integer|exists:regions,id',
           'repeat_type' => 'integer|in:'.implode(',', RepeatType::getValues()),
           'posting_start_date' => 'required|date',
           'posting_end_date' => 'nullable|date|after_or_equal:posting_start_date',
           'placements' => 'required|array|min:1',
           'placements.*.workplace_id' => 'nullable|integer',
           'placements.*.placement_type_id' => 'required|integer',
           'placements.*.employee_id' => 'nullable|integer',
           'placements.*.report_at' => 'required|date',
           'placements.*.start_at' => 'required|date',
           'placements.*.end_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $address = Address::findOrFail($request->input('address_id'));
            $this->canAccess($address);

            DB::beginTransaction();
            $postings = $this->postingService->store($request->only([
                'name',
                'address_id',
                'dresscode',
                'briefing',
                'information',
                'agencies',
                'regions',
                'repeat_type',
                'posting_start_date',
                'posting_end_date',
            ]));

            foreach ($postings as $posting) {
                $placements = $this->placementService->storeBulk($posting, $request->input('placements'));
            }

            $postings = $this->postingService->getBulk($postings->pluck('id')->all());
            DB::commit();
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($postings);
    }

    public function update(Request $request, Posting $posting)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'string|max:255',
           'dresscode' => 'string|max:255',
           'briefing' => 'string|max:255',
           'information' => 'string|max:255',
           'agencies' => 'array|min:1',
           'agencies.*' => 'integer|exists:agencies,id',
           'regions' => 'array|min:1',
           'regions.*' => 'integer|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin', 'client']);
            $this->canAccess($posting);

            $posting = $this->postingService->update($request->only([
                'name',
                'dresscode',
                'briefing',
                'information',
                'agencies',
                'regions',
            ]), $posting);

            $posting = $this->postingService->get($posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function show(Posting $posting)
    {
        try {
            $this->canAccess($posting);

            $posting = $this->postingService->get($posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function cancel(Posting $posting)
    {
        try {
            $this->rolesCanAccess(['admin', 'client']);
            $this->canAccess($posting);

            $posting = $this->postingService->cancel($posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }
}
