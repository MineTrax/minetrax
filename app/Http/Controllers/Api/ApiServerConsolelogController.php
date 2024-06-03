<?php

namespace App\Http\Controllers\Api;

use App\Events\ServerConsolelogCreated;
use App\Models\ServerConsolelog;
use Illuminate\Http\Request;

class ApiServerConsolelogController extends ApiController
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'data.log' => 'required',
            'data.server_id' => 'required|exists:servers,id',
        ]);

        // Split each log into 50000 (<65535) bytes max (MySQL TEXT column limit)
        $logs = str_split($request->input('data.log'), 50000);

        foreach ($logs as $log) {
            $log = ServerConsolelog::create([
                'server_id' => $request->input('data.server_id'),
                'data' => $log,
            ]);

            broadcast(new ServerConsolelogCreated($log));
        }

        return $this->success(null, 'Ok', 200);
    }
}
