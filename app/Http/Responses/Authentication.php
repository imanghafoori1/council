<?php

namespace App\Http\Responses;

class Authentication
{
    public function handle()
    {
        if (request()->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}