<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class VerifyN8nCallback
{
    /**
     * Handle callback depends on the mode (bearer or JWT)
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = (string) Config::get('n8n.auth_mode', 'bearer');

        if ($mode === 'bearer') {
            $token = (string) Config::get('n8n.bearer_token');
            $provided = (string) $request->bearerToken();

            if (!$token || !hash_equals($token, $provided)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } elseif ($mode === 'hmac') {
            $secret = (string) Config::get('n8n.hmac_secret');
            $provided = (string) $request->headers->get('X-Signature', '');
            $raw = $request->getContent();
            $expected = base64_encode(hash_hmac('sha256', (string) $raw, (string) $secret, true));

            if (!$secret || !$provided || !hash_equals($expected, $provided)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        return $next($request);
    }
}


