<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
            $users = $this->userService->list($perPage, $search);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($users);
    }

    public function show(User $user)
    {
        try {
            $user = $this->userService->get($user);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($user);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255',
            'agency_id' => 'nullable|integer|exists:agencies,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'role' => 'required|string|in:admin,agent,client',
            'locations' => 'array|min:1',
            'locations.*' => 'integer|exists:locations,id',

        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $user = $this->userService->store($request->only([
                'first_name',
                'last_name',
                'email',
                'agency_id',
                'client_id',
                'role',
                'locations',
            ]));
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($user);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'email|string|max:255',
            'locations' => 'array|min:1',
            'locations.*' => 'integer|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return $this->failedValidationResponse($validator);
        }

        try {
            $user = $this->userService->update($request->only([
                'first_name',
                'last_name',
                'email',
                'locations',
            ]), $user);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->successResponse($user);
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $this->messageResponse('User removed successfully.');
    }
}
