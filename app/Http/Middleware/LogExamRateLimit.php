<?php

namespace App\Http\Middleware;

use App\Models\ExamActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogExamRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 429 && $session = $request->route('session')) {
            if ($session && $session->user_id === auth()->id() && $session->status === 'in_progress') {
                ExamActivityLog::record($session->id, 'rate_limit');
                $terminateEvents = $session->exam->terminate_on_events ?? [];
                if (in_array('rate_limit', $terminateEvents)) {
                    $session->terminateForViolation();
                }
            }
        }

        return $response;
    }
}
