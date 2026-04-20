<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function requester(): JsonResponse
    {
        return response()->json(['message' => 'API requester dashboard endpoint is not yet implemented.'], 501);
    }

    public function approver(): JsonResponse
    {
        return response()->json(['message' => 'API approver dashboard endpoint is not yet implemented.'], 501);
    }

    public function hr(): JsonResponse
    {
        return response()->json(['message' => 'API HR dashboard endpoint is not yet implemented.'], 501);
    }

    public function ict(): JsonResponse
    {
        return response()->json(['message' => 'API ICT dashboard endpoint is not yet implemented.'], 501);
    }

    public function admin(): JsonResponse
    {
        return response()->json(['message' => 'API admin dashboard endpoint is not yet implemented.'], 501);
    }
}

