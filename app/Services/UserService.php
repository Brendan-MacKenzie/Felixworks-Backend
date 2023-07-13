<?php

namespace App\Services;

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

        return $user->load('agency', 'client', 'roles', 'permissions');
    }

    public function update(array $data, mixed $user)
    {
    }

    public function delete(mixed $user)
    {
    }

    public function get(mixed $user)
    {
    }

    public function list(int $perPage = 25, string $query = null)
    {
    }
}
