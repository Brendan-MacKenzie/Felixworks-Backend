<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Services\AddressService;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    private $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    //store address
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'street_name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'workplaces' => 'required|array',
            'workplaces.*.name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $address = $this->addressService->store($request->only([
                'name',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
                'workplaces',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($address);
    }

    public function update(Request $request, Address $address)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'street_name' => 'required_with:number,zip_code,city,country|string|max:255',
            'number' => 'required_with:street_name,zip_code,city,country|string|max:255',
            'zip_code' => 'required_with:street_name,number,city,country|string|max:255',
            'city' => 'required_with:street_name,number,zip_code,country|string|max:255',
            'country' => 'required_with:street_name,number,zip_code,city|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $address = $this->addressService->update($request->only([
                'name',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
            ]), $address);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($address);
    }

    public function destroy(Address $address)
    {
        try {
            $this->addressService->delete($address);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Address and associated workplaces removed successfully');
    }
}
