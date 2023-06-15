<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Services\ClientService;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
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
            $clients = $this->clientService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($clients);
    }

    public function show(Client $client)
    {
        try {
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
            $client = $this->clientService->store($request->only([
                'name',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($client);
    }
}
