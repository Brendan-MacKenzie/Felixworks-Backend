<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use BrendanMacKenzie\AuthServerClient\AuthServer;

class UserService extends Service
{
    private $authServerClient;

    public function __construct(AuthServer $authServerClient)
    {
        $this->authServerClient = $authServerClient;
    }

    public function store(array $data)
    {
        $data['created_by'] = (Auth::check()) ? Auth::user()->id : null;

        $user = User::create($data);
        if (isset($data['locations'])) {
            $user->locations()->sync($data['locations']);
        }

        // Assign role and attach to agency or client based on the role
        if (isset($data['role'])) {
            $role = $data['role'];
            $user->assignRole($role);

            if ($role == 'admin') {
                $user->agency_id = null;
                $user->client_id = null;
            } elseif ($role == 'agent' && isset($data['agency_id'])) {
                $user->agency_id = $data['agency_id'];
            } elseif ($role == 'client' && isset($data['client_id'])) {
                $user->client_id = $data['client_id'];
            }

            $user->save();  // Save the updated user record
        }

        $authProfile = $this->authServerClient->register($user, false, null);
        $user->profile_id = $authProfile->getProfileId();
        $user->save();

        return $user->load('agency', 'client', 'roles', 'permissions', 'locations');
    }

    public function update(array $data, mixed $user)
    {
        $user->update($data);

        if (isset($data['locations'])) {
            $user->locations()->sync($data['locations']);
        }

        return $user->load('agency', 'client', 'roles', 'permissions', 'locations');
    }

    public function delete(mixed $user)
    {
        // throw exception if the user has the admin role.
        if ($user->hasRole('admin')) {
            throw new Exception('Cannot delete an admin user');
        }

        // if the user has the client role and the client only has 1 user.
        if ($user->hasRole('client') && $user->client_id !== null) {
            $numberOfUsersWithSameClient = User::where('client_id', $user->client_id)->count();
            if ($numberOfUsersWithSameClient <= 1) {
                throw new Exception('Cannot delete this user. The client associated has only this one user.');
            }
        }

        // if the user has the agency role and the agency only has 1 user.
        if ($user->hasRole('agent') && $user->agency_id !== null) {
            $numberOfUsersWithSameAgency = User::where('agency_id', $user->agency_id)->count();
            if ($numberOfUsersWithSameAgency <= 1) {
                throw new Exception('Cannot delete this user. The agency associated has only this one user.');
            }
        }

        // else delete the user.
        $user->delete();
    }

    public function get(mixed $user)
    {
        return $user->load('agency', 'client', 'roles', 'permissions', 'locations', 'settings');
    }

    public function list(int $perPage = 25, string $query = null)
    {
        return User::with([
            'agency',
            'client',
            'roles',
            'permissions',
            'locations',
            'settings',
        ])
        ->when($query, function ($q) use ($query) {
            $q->where('first_name', 'like', '%'.$query.'%')
                ->orWhere('last_name', 'like', '%'.$query.'%')
                ->orWhere('email', 'like', '%'.$query.'%');
        })->paginate($perPage);
    }
}
