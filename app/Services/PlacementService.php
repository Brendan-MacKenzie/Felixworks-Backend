<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Posting;
use App\Models\Employee;
use App\Models\Placement;
use App\Models\Workplace;
use App\Models\PlacementType;
use App\Enums\PlacementStatus;
use App\Enums\AgencyActionType;
use App\Services\Sync\SyncHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PlacementService extends Service
{
    public function store(array $data, bool $notifySync = true)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        $posting = Posting::findOrFail($data['posting_id']);

        $placementType = PlacementType::findOrFail($data['placement_type_id']);

        $workplace = (key_exists('workplace_id', $data)) ?
        Workplace::findOrFail($data['workplace_id']) :
        null;

        $employee = (key_exists('employee_id', $data)) ?
            Employee::findOrFail($data['employee_id']) :
            null;

        $this->validatePlacement($posting, $placementType, $workplace, $employee);

        $data = $this->checkDates($data, $posting);

        $data = $this->checkStatus($data, $employee);

        $placement = Placement::create($data);

        if ($notifySync) {
            // Make action job for agencies.
            SyncHelper::syncInBulk(
                $placement->posting->agencies,
                $placement->posting,
                AgencyActionType::PostingUpdate,
                $posting->id
            );
        }

        return $placement;
    }

    public function storeBulk(Posting $posting, array $placements)
    {
        $data = collect($placements);
        foreach ($placements as $placement) {
            $amount = $placement['amount'] ?? 1; // Default to 1 if 'amount' is not set

            // Remove the 'amount' attribute so it doesn't interfere with the creation of the placement
            unset($placement['amount']);
            for ($i = 0; $i < $amount; $i++) {
                $placement['posting_id'] = $posting->id;
                $createdPlacement = $this->store($placement, false);
                $data->push($createdPlacement);
            }
        }

        // Make action job for agencies.
        SyncHelper::syncInBulk(
            $posting->agencies,
            $posting,
            AgencyActionType::PostingUpdate,
            $posting->id
        );

        return $data;
    }

    public function update(array $data, mixed $placement)
    {
        if ($placement->status == PlacementStatus::Registered || $placement->hours || $placement->registered_at) {
            throw new Exception('Placement is already registered.', 403);
        }

        $posting = $placement->posting;

        $placementType = (key_exists('placement_type_id', $data)) ?
        PlacementType::findOrFail($data['placement_type_id']) :
        $placement->placementType;

        $workplace = (key_exists('workplace_id', $data)) ?
        Workplace::findOrFail($data['workplace_id']) :
        $placement->workplace;

        $employee = (key_exists('employee_id', $data)) ?
            Employee::findOrFail($data['employee_id']) :
            $placement->employee;

        $this->validatePlacement($posting, $placementType, $workplace, $employee);

        $data = $this->checkStatus($data, $employee);

        $data = $this->checkDates($data, $posting);

        if (key_exists('hours', $data) && !is_null($data['hours'])) {
            if (!$employee) {
                throw new Exception("You can't register an empty placement.", 500);
            }

            $data['registered_at'] = Carbon::now();
        }

        $placement->update($data);
        $placement->refresh();

        // Make action job for agencies.
        SyncHelper::syncInBulk(
            $placement->posting->agencies,
            $placement->posting,
            AgencyActionType::PostingUpdate,
            $posting->id
        );

        return $placement;
    }

    public function delete(mixed $placement)
    {
    }

    public function get(mixed $placement)
    {
        $placement->load([
            'posting',
            'workplace',
            'placementType',
            'employee',
            'agency',
        ]);
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }

    public function fill(Placement $placement, Employee $employee)
    {
        DB::beginTransaction();
        $posting = $placement->posting;
        $location = $posting->workAddress->model;
        $address = $posting->workAddress;

        if (config('app.PLAN_HOURS') > 0 && $placement->start_at->diffInHours(Carbon::now()) <= config('app.PLAN_HOURS')) {
            throw new Exception('The time limit of '.config('app.PLAN_HOURS').' hours to plan someone on this placement is exceeded.', 403);
        }

        if ($placement->employee_id && $placement->employee_id !== $employee->id) {
            throw new Exception('Placement is occupied.', 403);
        }

        $this->validatePlacement($posting, null, null, $employee);

        $placement->employee_id = $employee->id;
        $placement->status = PlacementStatus::Confirmed;
        $placement->save();

        if ($location && $address) {
            $address
                ->employees()
                ->syncWithoutDetaching([$employee]);

            $location
                ->employees()
                ->syncWithoutDetaching([$employee]);
        }

        DB::commit();

        // Notify agencies from change except the agency in call.
        SyncHelper::syncInBulk(
            $placement->posting->agencies->except($placement->employee->agency_id),
            $placement->posting,
            AgencyActionType::PostingUpdate,
            $placement->posting_id
        );

        return $placement;
    }

    public function empty(Placement $placement)
    {
        DB::beginTransaction();
        $posting = $placement->posting;
        $location = $posting->workAddress->model;
        $address = $posting->workAddress;

        if ($placement->start_at->diffInHours(Carbon::now()) <= config('app.CANCEL_HOURS')) {
            throw new Exception('Placement cannot be cancelled within '.config('app.CANCEL_HOURS').' hours.', 403);
        }

        if ($placement->status == PlacementStatus::Registered || $placement->hours || $placement->registered_at) {
            throw new Exception('Placement is already registered.', 403);
        }

        if (!$placement->employee) {
            throw new Exception('Placement is already empty.', 500);
        }

        $employee = $placement->employee;
        $placement->employee_id = null;
        $placement->status = PlacementStatus::Open;
        $placement->save();

        // Remove employee from location if it has not worked before.
        if ($location && $address) {
            $otherPlacementsWithAddressExist = Placement::whereHas('posting', function ($q) use ($address) {
                $q->where('address_id', $address->id);
            })
                ->where('employee_id', $employee->id)
                ->where('id', '!=', $placement->id)
                ->exists();

            if (!$otherPlacementsWithAddressExist) {
                $address
                    ->employees()
                    ->detach([$employee]);
            }

            $otherPlacementsWithLocationExist = Placement::whereHas('posting', function ($q) use ($location) {
                $q->where('location_id', $location->id);
            })
                ->where('employee_id', $employee->id)
                ->where('id', '!=', $placement->id)
                ->exists();

            if (!$otherPlacementsWithLocationExist) {
                $location
                    ->employees()
                    ->detach([$employee]);
            }
        }

        // Remove employee if not used on other placements and agency is connected to an external system.
        if ($employee->agency->webhook && $employee->placements()->where('id', '!=', $placement->id)->count() <= 0) {
            $employee->forceDelete();
        }

        DB::commit();

        // Notify agencies from change except the agency in call.
        SyncHelper::syncInBulk(
            $placement->posting->agencies->except($placement->employee->agency_id),
            $placement->posting,
            AgencyActionType::PostingUpdate,
            $placement->posting_id
        );

        return $placement;
    }

    private function validatePlacement(
        mixed $posting,
        mixed $placementType = null,
        mixed $workplace = null,
        mixed $employee
    ) {
        $workAddress = $posting->workAddress;

        // Check if placement_type is from same location
        if ($placementType && $placementType->location_id !== $workAddress->model->id) {
            throw new Exception('Placement type does not exist in this location.', 500);
        }

        // Check if posting address_id = workplace address_id
        if ($workplace && $workplace->address_id !== $workAddress->id) {
            throw new Exception('Workplace does not exist on this workaddress.', 500);
        }

        // Check if employee is from allowed agency
        if ($employee && !in_array($employee->agency_id, $posting->agencies->pluck('id')->all())) {
            throw new Exception('Employee is required to be from a linked agency.', 500);
        }
    }

    private function checkStatus(array &$data, mixed $employee = null)
    {
        $status = PlacementStatus::Open;
        if ($employee) {
            $status = (key_exists('hours', $data) && !is_null($data['hours'])) ? PlacementStatus::Registered : PlacementStatus::Confirmed;
        }

        $data['status'] = $status;

        return $data;
    }

    private function checkDates(array &$data, Posting $posting)
    {
        $reportAt = Carbon::parse($posting->start_at->toDateString().' '.Carbon::parse($data['report_at'])->format('H:i:s'));
        $startAt = Carbon::parse($posting->start_at->toDateString().' '.Carbon::parse($data['start_at'])->format('H:i:s'));
        $endAt = Carbon::parse($posting->start_at->toDateString().' '.Carbon::parse($data['end_at'])->format('H:i:s'));

        if ($reportAt->gt($startAt)) {
            throw new Exception('Report at has to before the start at.', 500);
        }

        if ($endAt->lt($startAt)) {
            $endAt->addDay();
        }

        $data['report_at'] = $reportAt;
        $data['start_at'] = $startAt;
        $data['end_at'] = $endAt;

        return $data;
    }
}
