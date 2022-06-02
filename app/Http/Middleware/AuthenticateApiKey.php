<?php

namespace App\Http\Middleware;

use App\Settings\PluginSettings;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{

    public function __construct(public PluginSettings $pluginSettings) {}

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->pluginSettings->enable_api) {
            return response()->json([
                'message' => 'API Access is Disabled'
            ], 422);
        }

        // Get from either header X-API-KEY and X-API-SECRET or from body api_key and api_secret
        $apiKey = $request->input('api_key') ?? $request->header('X-API-KEY');
        $apiSecret = $request->input('api_secret') ?? $request->header('X-API-SECRET');

        if ($apiKey != $this->pluginSettings->plugin_api_key || $apiSecret != $this->pluginSettings->plugin_api_secret) {
            return response()->json([
                'message' => 'Please provide valid API credentials'
            ], 401);
        }

        return $next($request);
    }
}
