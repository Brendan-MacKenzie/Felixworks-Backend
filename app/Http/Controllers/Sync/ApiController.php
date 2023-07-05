<?php

namespace App\Http\Controllers\Sync;

use Exception;
use App\Models\Agency;
use App\Models\Posting;
use App\Enums\MediaType;
use App\Models\Employee;
use App\Models\Placement;
use App\Models\Declaration;
use Illuminate\Http\Request;
use App\Jobs\AgencyActionJob;
use App\Services\MediaService;
use App\Enums\AgencyActionType;
use App\Services\PostingService;
use App\Services\EmployeeService;
use App\Services\PlacementService;
use App\Http\Controllers\Controller;
use App\Services\DeclarationService;
use Illuminate\Support\Facades\Validator;
use App\Services\Sync\QueueTracker\AgencyQueueTracker;

class ApiController extends Controller
{
    private $postingService;
    private $placementService;
    private $mediaService;
    private $employeeService;
    private $declarationService;
    private $agency;

    public function __construct(
        PostingService $postingService,
        PlacementService $placementService,
        MediaService $mediaService,
        EmployeeService $employeeService,
        DeclarationService $declarationService
    ) {
        $this->postingService = $postingService;
        $this->placementService = $placementService;
        $this->mediaService = $mediaService;
        $this->employeeService = $employeeService;
        $this->declarationService = $declarationService;
        $this->agency = $this->getAuth(request()->cookie('client_id', request()->header('X-Client-Id')));
    }

    public function sync(Posting $posting)
    {
        try {
            $this->hasAgencyAccess($this->agency, $posting);
            $posting = $this->postingService->syncAgency($this->agency, $posting);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($posting);
    }

    public function managePlacement(Request $request, Placement $placement, string $type)
    {
        $this->hasAgencyAccess($this->agency, $placement);

        switch($type) {
            case 'fill':
                // Validate input
                $validator = Validator::make($request->all(), [
                    'external_id' => 'required|string|max:255',
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
                    $request->merge(['agency_id' => $this->agency->id]);
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
                        $job = new AgencyActionJob($this->agency, AgencyActionType::SendAvatar, $employee->id);
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
        foreach ($placement->posting->agencies->except($this->agency->id) as $agency) {
            $job = new AgencyActionJob($agency, AgencyActionType::PostingUpdate, $placement->posting_id);
            AgencyQueueTracker::setJob($job);
        }

        return $this->successResponse($placement->only('posting_id', 'employee_id', 'id'));
    }

    public function uploadAvatar(Request $request, Employee $employee)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'media' => 'required|image|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $request->merge(['type' => MediaType::Avatar]);

        try {
            $this->hasAgencyAccess($this->agency, $employee);
            $media = $this->mediaService->store($request->only([
                'media',
                'type',
            ]));

            $employee = $this->employeeService->update([
                'avatar_uuid' => $media->id,
            ], $employee);
        } catch (Exception $exception) {
            if ($media) {
                $this->mediaService->delete($media);
            }

            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Image received');
    }

    public function createOrUpdateEmployee(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'external_id' => 'required|string|max:255',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'date_of_birth' => 'date',
            'drivers_license' => 'boolean',
            'car' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $request->merge(['agency_id' => $this->agency->id]);
            $employee = $this->employeeService->store($request->only([
                'agency_id',
                'external_id',
                'first_name',
                'last_name',
                'date_of_birth',
                'drivers_license',
                'car',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($employee);
    }

    public function registerHours(Request $request, Placement $placement)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'hours' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->hasAgencyAccess($this->agency, $placement);
            $placement = $this->placementService->update($request->only([
                'hours',
            ]), $placement);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($placement);
    }

    public function storeDeclaration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'total' => 'required|integer',
            'placement_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $placement = Placement::find($request->input('placement_id'));

            if (!$placement) {
                throw new Exception('Placement not found.', 404);
            }

            $this->hasAgencyAccess($this->agency, $placement);
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

    public function updateDeclaration(Request $request, Declaration $declaration)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'total' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->hasAgencyAccess($this->agency, $declaration);
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

    public function destroyDeclaration(Declaration $declaration)
    {
        try {
            $this->hasAgencyAccess($this->agency, $declaration);
            $declaration = $this->declarationService->delete($declaration);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Declaration removed successfully.');
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

        if ($model instanceof Declaration && $model->placement->employee && !$agency->employees()->where('id', $model->placement->employee_id)->exists()) {
            throw new Exception('This declaration is not from your agency.', 403);
        }
    }
}
