<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AskDbChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class AskDbController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:use ask_db');
    }

    public function index(Request $request, AskDbChatService $askDbChatService)
    {
        $featureEnabled = config('minetrax.askdb_enabled');

        $markdownConverter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $chatHistory = [];
        foreach ($askDbChatService->history($request->user()) as $message) {
            $chatHistory[] = [
                'type' => $message['type'],
                'content' => $message['type'] === 'assistant'
                    ? $markdownConverter->convert($message['content'])->getContent()
                    : $message['content'],
            ];
        }

        return Inertia::render('Admin/AskDb/IndexAskDb', [
            'featureEnabled' => $featureEnabled,
            'chatHistory' => $chatHistory,
            'appDebug' => config('app.debug'),
        ]);
    }

    public function query(Request $request, AskDbChatService $askDbChatService)
    {
        $featureEnabled = config('minetrax.askdb_enabled');
        $appDebug = config('app.debug');
        if (! $featureEnabled) {
            return response()->json([
                'message' => __('This feature is not enabled!'),
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        try {
            $response = $askDbChatService->chat($request->prompt, $request->user());

            $converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]);
            $responseText = $converter->convert($response->text);

            return response()->json([
                'data' => [
                    'type' => 'assistant',
                    'content' => $responseText->getContent(),
                    'usage' => [
                        'promptTokens' => $response->usage->promptTokens,
                        'completionTokens' => $response->usage->completionTokens,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => 'Failed processing your request! Try again after rephrasing your question.',
                'verbose' => $appDebug ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function reset(Request $request, AskDbChatService $askDbChatService)
    {
        $askDbChatService->reset($request->user());

        return redirect()->back();
    }
}
