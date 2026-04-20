<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function export(): JsonResponse
    {
        return response()->json(['message' => 'API audit log export endpoint is not yet implemented.'], 501);
    }
}

