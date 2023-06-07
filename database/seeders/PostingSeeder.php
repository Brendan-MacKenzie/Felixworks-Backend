<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Employee;
use App\Models\Placement;
use App\Models\PlacementType;
use App\Models\Pool;
use App\Models\Posting;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Workplace;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Region::all()->pluck('id')->all();
        $sectors = Sector::all()->pluck('id')->all();
        $pools = Pool::all()->pluck('id')->all();

        $startAt = Carbon::now()->addDays(7);

        // Posting
        $posting = Posting::updateOrCreate([
            'name' => 'Posting 1',
            'address_id' => 1,
            'start_at' => $startAt,
            'dresscode' => 'Dit is de dresscode',
            'briefing' => 'Dit is de briefing',
            'information' => 'Dit is informatie.',
        ]);
        $posting->regions()->attach($regions);
        $posting->sectors()->attach($sectors);

        // Employees
        $employee = Employee::updateOrCreate([
            'agency_id' => 1,
            'external_id' => 1,
            'first_name' => 'Employee',
            'last_name' => 'One',
            'date_of_birth' => '1997-12-24',
            'drivers_license' => true,
            'car' => true,
        ]);
        $employee->pools()->attach($pools);

        $employee = Employee::updateOrCreate([
            'agency_id' => 1,
            'external_id' => 1,
            'first_name' => 'Employee',
            'last_name' => 'Two',
            'date_of_birth' => '2000-11-19',
            'drivers_license' => true,
            'car' => false,
        ]);

        // Placements
        foreach (Employee::all() as $employee) {
            $placement = Placement::updateOrCreate([
                'posting_id' => $posting->id,
                'workplace_id' => Workplace::all()->random()->id,
                'placement_type_id' => PlacementType::all()->random()->id,
                'employee_id' => $employee->id,
                'report_at' => $startAt,
                'start_at' => $startAt,
                'end_at' => $startAt->copy()->addHours(5),
            ]);
        }

        $placement = Placement::updateOrCreate([
            'posting_id' => $posting->id,
            'workplace_id' => Workplace::all()->random()->id,
            'placement_type_id' => PlacementType::all()->random()->id,
            'report_at' => $startAt,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addHours(5),
        ]);

        // Posting agencies
        $agencies = Agency::all()->pluck('id')->all();
        $posting->agencies()->attach($agencies);

        // Commitments
        $agencies = Agency::all()->pluck('id')->all();
        $posting->commitments()->sync($agencies, ['amount' => 1]);
    }
}
