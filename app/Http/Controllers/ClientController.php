<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use App\Services\Access\AccessManager;
use Illuminate\Http\Request;
use App\Services\ClientService;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use AccessManager;

    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        $search = $request->input('search', null);
        $perPage = $request->input('per_page', 25);

        try {
            $this->rolesCanAccess(['admin']);
            $clients = $this->clientService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($clients);
    }

    public function show(Client $client)
    {
        try {
            $this->canAccess($client);
            $client = $this->clientService->get($client);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($client);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->rolesCanAccess(['admin']);
            $client = $this->clientService->store($request->only([
                'name',
            ]));

            $client = $this->clientService->get($client);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($client);
    }

    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $this->canAccess($client, true);
            $client = $this->clientService->update($request->only([
                'name',
            ]), $client);

            $client = $this->clientService->get($client);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($client);
    }
}
