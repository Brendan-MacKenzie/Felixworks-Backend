<?php

namespace App\Services;

use Exception;
use App\Models\Posting;
use App\Models\Employee;
use App\Models\Placement;
use App\Models\Workplace;
use App\Models\PlacementType;
use App\Enums\PlacementStatus;
use Illuminate\Support\Facades\Auth;

class PlacementService extends Service
{
    public function store(array $data)
    {
        $data['created_by'] = Auth::user()->id;

        $posting = Posting::findOrFail($data['posting_id']);
        $placementType = PlacementType::findOrFail($data['placement_type_id']);

        $workplace = (key_exists('workplace_id', $data['workplace_id'])) ?
        Workplace::findOrFail($data['workplace_id']) :
        null;

        $employee = (key_exists('employee_id', $data['employee_id'])) ?
            Employee::findOrFail($data['employee_id']) :
            null;

        $this->validatePlacement($posting, $placementType, $workplace, $employee);

        $data = $this->checkStatus($data, $employee);

        $placement = Placement::create($data);

        return $placement;
    }

    public function update(array $data, mixed $placement)
    {
        $posting = $placement->posting;

        $placementType = (key_exists('placement_type_id', $data['placement_type_id'])) ?
        PlacementType::findOrFail($data['placement_type_id']) :
        $placement->placementType;

        $workplace = (key_exists('workplace_id', $data['workplace_id'])) ?
        Workplace::findOrFail($data['workplace_id']) :
        $placement->workplace;

        $employee = (key_exists('employee_id', $data['employee_id'])) ?
            Employee::findOrFail($data['employee_id']) :
            $placement->employee;

        $this->validatePlacement($posting, $placementType, $workplace, $employee);

        $data = $this->checkStatus($data, $employee);

        $placement->update($data);
        $placement->refresh();

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

    private function validatePlacement(
        mixed $posting,
        mixed $placementType,
        mixed $workplace,
        mixed $employee
    ) {
        $address = $posting->address;

        // Check if placement_type is from same branch
        if ($placementType && $placementType->branch_id !== $address->model->id) {
            throw new Exception('Placement type does not exist in this branch.', 500);
        }

        // Check if posting address_id = workplace address_id
        if ($workplace && $workplace->address_id !== $address->id) {
            throw new Exception('Workplace does not exist on this workaddress.', 500);
        }

        // Check if employee is from allowed agency
        if ($employee && !in_array($employee->agency_id, $posting->agencies->pluck('id')->all())) {
            throw new Exception('Employee is required to be from a linked agency.', 500);
        }
    }

    private function checkStatus(array &$data, mixed $employee = null)
    {
        $data['status'] = ($employee) ? PlacementStatus::Confirmed : PlacementStatus::Open;

        return $data;
    }
}
