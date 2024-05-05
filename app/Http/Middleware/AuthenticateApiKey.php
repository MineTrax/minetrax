<?php

namespace App\Http\Middleware;

use App\Settings\PluginSettings;
use App\Utils\Helpers\CryptoUtils;
use Closure;
use Illuminate\Http\Request;

const REQUEST_MAX_AGE_THRESHOLD_SECONDS = 60; // 60 seconds
const VALIDATE_SIGNATURE = true; // For testing purposes, we can disable signature validation.

class AuthenticateApiKey
{
    public function __construct(public PluginSettings $pluginSettings)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->pluginSettings->enable_api) {
            return response()->json([
                'status' => 'error',
                'type' => 'api_disabled',
                'message' => 'API Access is Disabled. Please enable it from Admin > Settings > Plugins.',
            ], 422);
        }

        // Get from either header X-API-KEY and X-API-SECRET or from body api_key and api_secret
        $apiKey = $request->header('X-API-KEY') ?? $request->input('x_api_key');
        $requestSignature = $request->header('X-SIGNATURE') ?? $request->input('x_signature');

        // Validate API Key
        if ($apiKey != $this->pluginSettings->plugin_api_key) {
            return response()->json([
                'status' => 'error',
                'type' => 'invalid_credentials',
                'message' => 'Please provide valid API credentials.',
            ], 401);
        }

        if (! $requestSignature) {
            return response()->json([
                'status' => 'error',
                'type' => 'signature_missing',
                'message' => 'Request Signature is missing.',
            ], 401);
        }

        // Validate Signature
        if ($request->method() == 'GET') {
            $signaturePayload = $request->getUri();
        } else {
            $signaturePayload = $request->getContent();
        }
        $signatureValid = CryptoUtils::validateHmacSignature($signaturePayload, $requestSignature, $this->pluginSettings->plugin_api_secret);
        if (! $signatureValid && VALIDATE_SIGNATURE) {
            return response()->json([
                'status' => 'error',
                'type' => 'invalid_signature',
                'message' => 'Invalid Request Signature.',
            ], 401);
        }

        // Validate Request Age if provided
        $timestampMs = $request->get('timestamp');
        if ($timestampMs && VALIDATE_SIGNATURE) {
            $timestamp = $timestampMs / 1000;
            $currentTimestamp = now()->timestamp;
            $requestAge = $currentTimestamp - $timestamp;
            if ($requestAge > REQUEST_MAX_AGE_THRESHOLD_SECONDS) {
                return response()->json([
                    'status' => 'error',
                    'type' => 'request_expired',
                    'message' => 'Request is too old.',
                ], 401);
            }
        }

        return $next($request);
    }
}
