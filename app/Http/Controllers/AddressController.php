<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Address;
use App\Enums\AddressType;
use Illuminate\Http\Request;
use App\Services\AddressService;
use App\Services\Access\AccessManager;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    use AccessManager;

    private $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function index()
    {
        try {
            $this->rolesCanAccess(['admin']);
            $addresses = $this->addressService->list();
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($addresses);
    }

    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|integer|in:'.implode(',', AddressType::getValues()),
            'street_name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'model_type' => 'required_if:type,0,nullable|string|max:255',
            'model_id' => 'required_if:type,0|nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $model = app($request->input('model_type'));
            $object = $model->find($request->input('model_id'));

            if (!$object) {
                throw new Exception('Could not find model.', 404);
            }

            $this->canAccess($object);

            $address = $this->addressService->store($request->only(
                [
                'name',
                'type',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
            ],
                $object
            ));

            $address = $this->addressService->get($address);
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
            $this->canAccess($address);

            $address = $this->addressService->update($request->only([
                'name',
                'street_name',
                'number',
                'zip_code',
                'city',
                'country',
            ]), $address);

            $address = $this->addressService->get($address);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($address);
    }

    public function destroy(Address $address)
    {
        try {
            $this->canAccess($address);

            $this->addressService->delete($address);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('Address and associated workplaces removed successfully');
    }
}
