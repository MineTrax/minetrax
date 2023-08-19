<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AskGptService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AskDbController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:use ask_db');
    }

    public function index()
    {
        $featureEnabled = config('minetrax.askdb_enabled');

        return Inertia::render('Admin/AskDb/IndexAskDb', [
            'featureEnabled' => $featureEnabled,
        ]);
    }

    public function query(Request $request, AskGptService $askGptService)
    {
        $featureEnabled = config('minetrax.askdb_enabled');
        if (! $featureEnabled) {
            return response()->json([
                'message' => __('This feature is not enabled!'),
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        try {
            $response = $askGptService->askDb($request->prompt);

            return response()->json([
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to Query Database! Try again after rephrasing your question.',
            ], 500);
        }
    }
}
