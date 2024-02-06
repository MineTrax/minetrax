<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AskGptService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;

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
            'appDebug' => config('app.debug'),
        ]);
    }

    public function query(Request $request, AskGptService $askGptService)
    {
        $featureEnabled = config('minetrax.askdb_enabled');
        $appDebug = config('app.debug');
        if (! $featureEnabled) {
            return response()->json([
                'message' => __('This feature is not enabled!'),
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        try {
            $response = retry(3, function () use ($request, $askGptService) {
                return $askGptService->askDb($request->prompt);
            }, 100);

            $converter = new GithubFlavoredMarkdownConverter();
            $response = $converter->convertToHtml($response);

            return response()->json([
                'data' => $response->getContent(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to Query Database! Try again after rephrasing your question.',
                'verbose' => $appDebug ? $e->getMessage() : null,
            ], 500);
        }
    }
}
