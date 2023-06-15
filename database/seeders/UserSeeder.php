<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use BrendanMacKenzie\AuthServerClient\AuthServer;

class UserSeeder extends Seeder
{
    private $authServerClient;

    public function __construct(AuthServer $authServerClient)
    {
        $this->authServerClient = $authServerClient;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $client = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'api',
        ]);

        $agent = Role::firstOrCreate([
            'name' => 'agent',
            'guard_name' => 'api',
        ]);

        // Admin
        $user = User::updateOrCreate([
            'first_name' => 'Admin',
            'last_name' => 'Role',
            'email' => 'admin@felix.nl',
        ]);
        $user->assignRole($admin);

        $authProfile = $this->authServerClient->register($user, true, null);
        $user->profile_id = $authProfile->getProfileId();
        $user->save();

        // Client
        $user = User::updateOrCreate([
            'first_name' => 'Client',
            'last_name' => 'Role',
            'email' => 'client@felix.nl',
            'branch_id' => 1,
        ]);
        $user->assignRole('client');

        $authProfile = $this->authServerClient->register($user, true, null);
        $user->profile_id = $authProfile->getProfileId();
        $user->save();

        // Agency
        $agent = User::updateOrCreate([
            'first_name' => 'Agent',
            'last_name' => 'Role',
            'email' => 'agent@felix.nl',
            'agency_id' => 1,
        ]);
        $user->assignRole('agent');

        $authProfile = $this->authServerClient->register($user, true, null);
        $user->profile_id = $authProfile->getProfileId();
        $user->save();
    }
}
