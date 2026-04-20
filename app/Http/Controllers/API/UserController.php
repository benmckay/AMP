<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => User::query()->latest()->paginate(15)]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => $user]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'API user creation endpoint is not yet implemented.'], 501);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        return response()->json(['message' => 'API user update endpoint is not yet implemented.'], 501);
    }

    public function destroy(User $user): JsonResponse
    {
        return response()->json(['message' => 'API user delete endpoint is not yet implemented.'], 501);
    }

    public function assignDepartment(Request $request, User $user): JsonResponse
    {
        return response()->json(['message' => 'API assign-department endpoint is not yet implemented.'], 501);
    }

    public function removeDepartment(User $user, $department): JsonResponse
    {
        return response()->json(['message' => 'API remove-department endpoint is not yet implemented.'], 501);
    }

    public function loginHistory(User $user): JsonResponse
    {
        return response()->json(['data' => []]);
    }
}

