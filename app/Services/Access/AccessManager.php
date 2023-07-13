<?php

namespace App\Services\Access;

use Exception;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Office;
use App\Models\Address;
use App\Models\Employee;
use App\Enums\AddressType;
use App\Enums\MediaType;
use App\Models\Commitment;
use App\Models\Declaration;
use App\Models\Location;
use App\Models\Media;
use App\Models\Placement;
use App\Models\PlacementType;
use App\Models\Pool;
use App\Models\Posting;
use App\Models\Workplace;
use Illuminate\Support\Facades\Auth;

trait AccessManager
{
    public function canAccess($model, bool $withPermissionCheck = false)
    {
        if (is_array($model)) {
            foreach ($model as $mod) {
                $this->canAccess($mod, $withPermissionCheck);
            }
        } else {
            try {
                switch($model) {
                    case $model instanceof Address:
                        return $this->canAccessAddress($model);
                    break;
                    case $model instanceof Agency:
                        return $this->canAccessAgency($model, $withPermissionCheck);
                    break;
                    case $model instanceof Client:
                        return $this->canAccessClient($model, $withPermissionCheck);
                    break;
                    case $model instanceof Commitment:
                        return $this->canAccessCommitment($model);
                    break;
                    case $model instanceof Declaration:
                        return $this->canAccessDeclaration($model);
                    break;
                    case $model instanceof Employee:
                        return $this->canAccessEmployee($model);
                    break;
                    case $model instanceof Location:
                        return $this->canAccessLocation($model);
                    break;
                    case $model instanceof Media:
                        return $this->canAccessMedia($model);
                    break;
                    case $model instanceof Office:
                        return $this->canAccessOffice($model);
                    break;
                    case $model instanceof Posting:
                        return $this->canAccessPosting($model);
                    break;
                    case $model instanceof Placement:
                        return $this->canAccessPlacement($model);
                    break;
                    case $model instanceof PlacementType:
                        return $this->canAccessPlacementType($model);
                    break;
                    case $model instanceof Pool:
                        return $this->canAccessPool($model);
                    break; 
                    case $model instanceof Workplace:
                        return $this->canAccessWorkplace($model);
                    break;
                }
            } catch (Exception $exception) {
                throw $exception;
            }
        }
    }

    /**
     * Models
     */

    public function canAccessAddress(Address $address)
    {
        // From agency
        if ($address->type == AddressType::Office) {
            // Auth needs to be admin or agent.
            $this->rolesCanAccess(['admin', 'agent']);

            // Auth needs to be from the same agency.
            if (
                Auth::user()->hasRole('agent') &&
                Auth::user()->agency_id !== $address->model->agency_id
            ) {
                throw new Exception("You don't have access to this agency.", 403);
            }
        }

        // From client
        if (
            $address->type == AddressType::WorkAddress ||
            $address->type == AddressType::Location
        ) {
            // Auth needs to be admin or client.
            $this->rolesCanAccess(['admin', 'client']);

            // Auth needs to be from the same location.
            if (
                Auth::user()->hasRole('client') &&
                !Auth::user()->locations()->exists($address->model)
            ) {
                throw new Exception("You don't have access to this location.", 403);
            }
        }

        return true;
    }

    public function canAccessAgency(Agency $agency, bool $withPermissionCheck = false)
    {
        // Auth needs to be admin or agency.
        $this->rolesCanAccess(['admin', 'agency']);

        // Auth needs to be from the agency.
        if (!Auth::user()->agency_id !== $agency->id) {
            throw new Exception("You don't have access to this agency.", 403);
        }

        // Auth needs to have the manage agency permission.
        if ($withPermissionCheck) {
            $this->permissionsCanAccess(['manage agency']);
        }

        return true;
    }

    public function canAccessClient(Client $client, bool $withPermissionCheck = false)
    {
        // Auth needs to be admin or client
        $this->rolesCanAccess(['admin', 'client']);

        // Auth needs to be from the client.
        if (Auth::user()->client_id !== $client->id) {
            throw new Exception("You don't have access to this client.", 403);
        }

        // Auth needs to have the manage client permission.
        if ($withPermissionCheck) {
            $this->permissionsCanAccess(['manage client']);
        }

        return true;
    }

    public function canAccessCommitment(Commitment $commitment)
    {
        // Auth needs to be admin or agent.
        $this->rolesCanAccess(['admin', 'agent']);

        // Auth needs to be from the agency.
        if (Auth::user()->agency_id !== $commitment->agency_id) {
            throw new Exception("You don't have access to this commitment.", 403);
        }

        return true;
    }

    public function canAccessDeclaration(Declaration $declaration)
    {
        // Auth needs to be admin or agent
        $this->rolesCanAccess(['admin', 'agent']);

        return $this->canAccessPlacement($declaration->placement);
    }

    public function canAccessEmployee(Employee $employee)
    {
        // Auth needs to be admin or agent.
        $this->rolesCanAccess(['admin', 'agent']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        // Auth needs to be from same agency as employee.
        if (Auth::user()->agency_id !== $employee->agency_id) {
            throw new Exception("You don't have access to this employee", 403);
        }

        return true;
    }

    public function canAccessLocation(Location $location)
    {
        // Auth needs to be admin or client
        $this->rolesCanAccess(['admin', 'client']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        // Auth needs to be from the same location.
        if (!Auth::user()->locations()->exists($location)) {
            throw new Exception("You don't have access to this location.", 403);
        }

        return true;
    }

    public function canAccessMedia(Media $media)
    {
        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        if (Auth::user()->hasRole('agent')) {
            if ($media->type == MediaType::Avatar) {
                // Check if avatar is from employee of agency.
                if (
                    $media->employee &&
                    $media->employee->agency_id !== Auth::user()->agency_id
                ) {
                    throw new Exception("You don't have access to this media.", 403);
                }
            }

            if ($media->type == MediaType::Logo) {
                // Check if logo is from agency.
                if (
                    $media->agency &&
                    $media->agency->id !== Auth::user()->agency_id
                ) {
                    throw new Exception("You don't have access to this media.", 403);
                }
            }

        } elseif (Auth::user()->hasRole('client')) {
            if ($media->type !== MediaType::Avatar) {
                throw new Exception("You don't have access to this media.", 403);
            }

            // Check if employee from avatar is linked with client.
            $locationsOfAuth = Auth::user()->locations->pluck('id')->all();
            if (
                $media->employee &&
                !$media->employee->locations()->whereIn('id', $locationsOfAuth)->exists()
            ) {
                throw new Exception("You don't have access to this media.", 403);
            }

        } else {
            throw new Exception("You don't have access to this media.", 403);
        }

        return true;
    }

    public function canAccessOffice(Office $office)
    {
        // Auth needs to be admin or agent
        $this->rolesCanAccess(['admin', 'agent']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        // Auth needs to be from same agency
        if (Auth::user()->agency_id !== $office->agency_id) {
            throw new Exception("You don't have access to this office.", 403);
        }

        return true;
    }

    public function canAccessPosting(Posting $posting)
    {
        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        if (Auth::user()->hasRole('client')) {
            // Auth is client.
            // Check if posting is from location connected to auth.
            if (!Auth::user()->locations()->exists($posting->address->model)) {
                throw new Exception("You don't have access to this posting.", 403);
            }
        } elseif(Auth::user()->hasRole('agent')) {
            // Auth is agent.
            // Check if agency has access to posting.
            if (!$posting->agencies()->exists(Auth::user()->agency)) {
                throw new Exception("You don't have access to this posting.", 403);
            }
        } else {
            throw new Exception("You don't have access to this posting.", 403);
        }

        return true;
    }

    public function canAccessPlacement(Placement $placement)
    {
        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        // If client, check if posting is from location of auth.
        if (
            Auth::user()->hasRole('client') &&
            !Auth::user()->locations()->exists($placement->posting->address->model)
        ) {
            throw new Exception("You don't have access to this placement.", 403);
        }

        // If agent, check if employee is from agency.
        if (
            $placement->employee &&
            Auth::user()->hasRole('agent') &&
            $placement->employee->agency_id !== Auth::user()->agency_id
        ) {
            throw new Exception("You don't have access to this placement.", 403);
        }

        // If agent, check if posting is linked to agency
        if (
            !$placement->employee &&
            Auth::user()->hasRole('agent') &&
            !$placement->posting->agencies()->exists(Auth::user()->agency)
        ) {
            throw new Exception("You don't have access to this placement.", 403);
        }

        return true;
    }

    public function canAccessPlacementType(PlacementType $placementType)
    {
        // Auth needs to be admin or client
        $this->rolesCanAccess(['admin', 'client']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        if (Auth::user()->hasRole('client')) {
            // Auth is client.
            // Check if placementtype is from location connected to auth.
            if (!Auth::user()->locations()->exists($placementType->location)) {
                throw new Exception("You don't have access to this placementtype.", 403);
            }
        } else {
            throw new Exception("You don't have access to this placementtype.", 403);
        }

        return true;

    }

    public function canAccessPool(Pool $pool)
    {
        // Auth needs to be admin or client
        $this->rolesCanAccess(['admin', 'client']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        if (Auth::user()->hasRole('client')) {
            // Auth is client.
            // Check if pool is from location connected to auth.
            if (!Auth::user()->locations()->exists($pool->location)) {
                throw new Exception("You don't have access to this pool.", 403);
            }
        } else {
            throw new Exception("You don't have access to this pool.", 403);
        }

        return true;
    }

    public function canAccessWorkplace(Workplace $workplace)
    {
        // Auth needs to be admin or client
        $this->rolesCanAccess(['admin', 'client']);

        // Check if admin
        if (Auth::user()->hasRole('admin')) {
            return true;
        }

        if (Auth::user()->hasRole('client')) {
            // Auth is client.
            // Check if workplace is from location connected to auth.
            if (!Auth::user()->locations()->exists($workplace->address->model)) {
                throw new Exception("You don't have access to this workplace.", 403);
            }
        } else {
            throw new Exception("You don't have access to this workplace.", 403);
        }

        return true;
    }

    /**
     * Other
     */

    public function rolesCanAccess(array $roles)
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            $this->throwRoleException();
        }

        return true;
    }

    public function permissionsCanAccess(array $permissions)
    {
        if (!Auth::user()->hasAnyPermission($permissions)) {
            $this->throwPermissionException();
        }

        return true;
    }

    private function throwRoleException()
    {
        throw new Exception("You don't have the right role for this action.", 403);
    }

    private function throwPermissionException()
    {
        throw new Exception("You don't have the right permissions for this action", 403);
    }
}
