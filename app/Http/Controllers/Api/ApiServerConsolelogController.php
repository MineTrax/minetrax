<?php

namespace App\Http\Controllers\Api;

use App\Events\ServerConsolelogCreated;
use App\Http\Controllers\Controller;
use App\Models\ServerConsolelog;
use Illuminate\Http\Request;

class ApiServerConsolelogController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'log' => 'required',
            'server_id' => 'required|exists:servers,id'
        ]);

        $log = ServerConsolelog::create([
            'server_id' => $request->server_id,
            'data' => $request->log
        ]);

        broadcast(new ServerConsolelogCreated($log));

        return response()->json([
            'status' => 'success',
            'message' => 'Ok'
        ], 201);
    }
}
