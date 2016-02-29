<?php

namespace App\Http\Middleware;


use Illuminate\Contracts\Auth\Guard;
use Closure;

class Authenticate {
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest())
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect()->guest('auth/login');
            }
        }

        if ($this->auth->User()->has2fa() && ! $request->session()->has('2faAuthed'))
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect()->guest('auth/otp');
            }
        }

        return $next($request);
    }
}
