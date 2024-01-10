<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\http\Middleware\TokenExpiredException;
use App\http\Middleware\JWTException;
class CheckUser
{

    public function handle(Request $request, Closure $next, $guard = 'null')
    {

    //     if(auth()->guard('user-api')->check() ) // && auth()->guard('user')->user()->type == admin
       return $next($request);
    // // else
    // //     return response()->josn(['message'=>'user is not looged in '],422);
    // //   //  abort(403);


    }
}
