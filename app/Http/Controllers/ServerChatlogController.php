<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\ServerChatlog;
use App\Utils\Helpers\MinecraftColorUtils;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerChatlogController extends Controller
{
    public function index($server, Request $request)
    {
        // AfterId for polling API.
        $afterId = $request->after;

        $query = ServerChatlog::orderByDesc('id')
            ->whereNotNull('data')
            ->where('server_id', $server)
            ->whereNull('channel');

        if ($afterId) {
            // What? Can we expect to get 100 chatlog within 6 seconds polling interval? No I guess...
            $query->where('id', '>', $afterId)->limit(100);
        } else {
            $query->limit(50);
        }

        return $query->get()
            ->map(function ($data) {
                $data->data = MinecraftColorUtils::convertToHTML($data->data);
                return $data;
            });
    }

    public function sendToServer(Server $server, Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:255'
        ]);

        $user = $request->user();
        $userDisplayName = $user->username;
        $webMessageFormat = $user->role_web_message_format;

        if ($webMessageFormat) {
            $userDisplayName = str_replace("{USERNAME}", $userDisplayName, $webMessageFormat);
        } else {
            $userDisplayName = $userDisplayName . '&r';
        }

        $webQuery = new MinecraftWebQuery($server->ip_address, $server->webquery_port);

        // If message starts with '/' then its a command
        if (Str::startsWith($request->message, '/') && $user->can('send server_custom_commands')) {
            return $webQuery->runCommand(
                Str::replaceFirst('/', '', $request->message)
            );
        } else {
            return $webQuery->sendChat($userDisplayName, $request->message);
        }
    }
}
