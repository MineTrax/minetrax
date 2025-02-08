<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AskDbService;
use EchoLabs\Prism\ValueObjects\Messages\AssistantMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class AskDbController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:use ask_db');
    }

    public function index(Request $request)
    {
        $featureEnabled = config('minetrax.askdb_enabled');

        $userId = $request->user()->id;
        $userChatSessionHistory = Cache::get("askdb::user_chat_session::{$userId}", []);

        $markdownConverter = new GithubFlavoredMarkdownConverter();
        $chatHistory = [];
        foreach ($userChatSessionHistory as $message) {
            if ($message instanceof AssistantMessage && $message->content) {
                $message = $markdownConverter->convertToHtml($message->content)->getContent();
                $chatHistory[] = [
                    'type' => 'assistant',
                    'content' => $message,
                ];
            } else if ($message instanceof UserMessage) {
                $chatHistory[] = [
                    'type' => 'user',
                    'content' => $message->text(),
                ];
            }
        }

        return Inertia::render('Admin/AskDb/IndexAskDb', [
            'featureEnabled' => $featureEnabled,
            'chatHistory' => $chatHistory,
            'appDebug' => config('app.debug'),
        ]);
    }

    public function query(Request $request, AskDbService $askDbService)
    {
        $featureEnabled = config('minetrax.askdb_enabled');
        $appDebug = config('app.debug');
        if (!$featureEnabled) {
            return response()->json([
                'message' => __('This feature is not enabled!'),
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        try {
            $response = $askDbService->chatWithAskDbForUser($request->prompt, $request->user());

            $converter = new GithubFlavoredMarkdownConverter();
            $responseText = $converter->convertToHtml($response->text);

            return response()->json([
                'data' => [
                    'type' => 'assistant',
                    'content' => $responseText->getContent(),
                    'usage' => $response->usage,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => 'Failed processing your request! Try again after rephrasing your question.',
                'verbose' => $appDebug ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function reset(Request $request)
    {
        $userId = $request->user()->id;
        Cache::forget("askdb::user_chat_session::{$userId}");

        return redirect()->back();
    }
}
