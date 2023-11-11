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
            'server_id' => 'required|exists:servers,id',
        ]);

        // Split each log into 50000 (<65535) bytes max (MySQL TEXT column limit)
        $logs = str_split($request->log, 50000);

        foreach ($logs as $log) {
            $log = ServerConsolelog::create([
                'server_id' => $request->server_id,
                'data' => $log,
            ]);

            broadcast(new ServerConsolelogCreated($log));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ok',
        ], 201);
    }
}
