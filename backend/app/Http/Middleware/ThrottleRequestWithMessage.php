<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestWithMessage extends ThrottleRequests
{
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);
        $message = "Terlalu banyak permintaan. Silakan coba lagi dalam {$retryAfter} detik.";
        return response()->json([
            'message' => $message,
            'retry_after' => $retryAfter
        ], 429);
    }
}
