<?php

namespace App\Http\Controllers\Sync;

use Exception;
use App\Models\Agency;
use App\Models\Posting;
use App\Enums\MediaType;
use App\Models\Employee;
use App\Models\Placement;
use Illuminate\Http\Request;
use App\Jobs\AgencyActionJob;
use App\Services\MediaService;
use App\Enums\AgencyActionType;
use App\Services\PostingService;
use App\Services\EmployeeService;
use App\Services\PlacementService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Sync\QueueTracker\AgencyQueueTracker;

class ApiController extends Controller
{
    private $postingService;
    private $placementService;
    private $mediaService;
    private $employeeService;

    public function __construct(
        PostingService $postingService,
        PlacementService $placementService,
        MediaService $mediaService,
        EmployeeService $employeeService
    ) {
        $this->postingService = $postingService;
        $this->placementService = $placementService;
        $this->mediaService = $mediaService;
        $this->employeeService = $employeeService;
    }

    public function sync(Request $request, Posting $posting)
    {
        try {
            $agency = $this->getAuth($request->cookie('client_id', $request->header('X-Client-Id')));
            $this->hasAgencyAccess($agency, $posting);
            $posting = $this->postingService->syncAgency($agency, $posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function managePlacement(Request $request, Placement $placement, string $type)
    {
        $agency = $this->getAuth($request->cookie('client_id', $request->header('X-Client-Id')));

        $this->hasAgencyAccess($agency, $placement);

        switch($type) {
            case 'fill':
                // Validate input
                $validator = Validator::make($request->all(), [
                    'external_id' => 'required|integer',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'date_of_birth' => 'required|date',
                    'drivers_license' => 'boolean',
                    'car' => 'boolean',
                ]);

                if ($validator->fails()) {
                    return $this->failedValidationResponse($validator);
                }

                try {
                    $request->merge(['agency_id' => $agency->id]);
                    $employee = $this->employeeService->store($request->only([
                        'agency_id',
                        'external_id',
                        'first_name',
                        'last_name',
                        'date_of_birth',
                        'drivers_license',
                        'car',
                    ]));

                    $placement = $this->placementService->fill($placement, $employee);

                    // If employee has no avatar, send avatar job to agency.
                    if (!$employee->avatar_uuid) {
                        $job = new AgencyActionJob($agency, AgencyActionType::SendAvatar, $employee->id);
                        AgencyQueueTracker::setJob($job);
                    }
                } catch (Exception $exception) {
                    return $this->failedExceptionResponse($exception);
                }
                break;
            case 'empty':
                try {
                    $placement = $this->placementService->empty($placement);
                } catch (Exception $exception) {
                    return $this->failedExceptionResponse($exception);
                }
                break;
            default:
                return $this->internalErrorResponse('Route not found.');
                break;
        }

        // Notify agencies from change except the agency in call.
        foreach ($placement->posting->agencies->except($agency->id) as $agency) {
            $job = new AgencyActionJob($agency, AgencyActionType::PostingUpdate, $placement->posting_id);
            AgencyQueueTracker::setJob($job);
        }

        return $this->successResponse($posting);
    }

    private function getAuth($clientId)
    {
        return Agency::findOrFail($clientId);
    }

    private function hasAgencyAccess(Agency $agency, mixed $model)
    {
        if ($model instanceof Placement && !$model->posting->agencies()->where('agency_id', $agency->id)->exists()) {
            throw new Exception('Agency does not have access to this posting.', 403);
        }

        if ($model instanceof Placement && $model->employee && !$agency->employees()->where('id', $model->employee_id)->exists()) {
            throw new Exception('This placement is not from your agency.', 403);
        }

        if ($model instanceof Employee && $model->agency_id !== $agency->id) {
            throw new Exception('Agency does not have access to this employee.', 403);
        }
    }
}
