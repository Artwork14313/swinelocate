<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replays the original result when the mobile app retries a write it queued
 * offline, so a lost response never creates a duplicate record.
 */
class IdempotentMobileRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key || ! $request->user() || strlen($key) > 100) {
            return $next($request);
        }

        $cacheKey = 'mobile-idempotency:'.$request->user()->getKey().':'.hash('sha256', $key);

        if ($stored = Cache::get($cacheKey)) {
            return response()->json($stored['body'], $stored['status'])
                ->header('Idempotent-Replay', 'true');
        }

        $response = $next($request);

        if ($response->getStatusCode() < 300) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getContent(), true),
            ], now()->addDays(7));
        }

        return $response;
    }
}
