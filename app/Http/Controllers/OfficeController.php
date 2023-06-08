<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    //store office
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'agency_id' => 'required|integer|exists:agencies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'street_name' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'created_by' => 'required|integer|exists:users,id',
        ]);

        // Create the office using the validated data
        $office = Office::create($validatedData);

        // Return the created office as JSON response:
        return response()->json($office, 201);
    }
}
