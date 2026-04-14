<?php
namespace App\Http\Middleware;

use App\Models\MacMachine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMacMachine
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Token manquant.'], 401);
        }

        $machine = MacMachine::where('token', $token)->first();

        if (! $machine) {
            return response()->json(['error' => 'Token invalide.'], 401);
        }

        $request->merge(['mac_machine' => $machine]);

        return $next($request);
    }
}
