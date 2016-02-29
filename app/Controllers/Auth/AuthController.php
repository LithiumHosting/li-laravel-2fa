<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\BaseController;
use App\Contracts\AuthenticateUserListener;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\CaptchaTrait;

class AuthController extends BaseController implements AuthenticateUserListener {
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    protected $redirectPath = '/';

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => ['getLogout']]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        if (\Auth::guard($this->getGuard())->validate($credentials))
        {
            $user = User::where('email', $request->input('email'))->firstOrFail();

            return $this->handleOtpOrLogin($request, $user, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles && ! $lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Show OTP authentication on login
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function getOtp(Request $request)
    {
        if (! $request->session()->get('password_validated'))
        {
            abort(404);
        }
        $user = User::where('id', $request->session()->get('id'))->firstOrFail();

        return view('auth.otp')->with($this->getPageVars());
    }

    /**
     * Process OTP authentication on login
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function postOtp(Request $request)
    {
        if (! $request->session()->get('password_validated'))
        {
            abort(404);
        }
        $user = User::where('id', $request->session()->get('id'))->firstOrFail();

        if (! $user->has2fa())
        {
            abort(404);
        }

        $psl          = new \phpSec\Core();
        $google       = $psl['auth/google'];
        $psl['store'] = $psl->share(function ($psl)
        {
            return new \phpSec\Store\File('/tmp', $psl); // Save files to /tmp
        });

        if ($google->verify($request->get('otp'), $user->otp_secret) === true)
        {
            $request->session()->put('2faAuthed', true);
            if ($request->session()->has('url_intended'))
            {
                $request->session()->put('url', $request->session()->pull('url_intended'));
            }

            $throttles = $this->isUsingThrottlesLoginsTrait();

            $this->auth->login($user, $request->session()->pull('remember'));
            return $this->handleUserWasAuthenticated($request, $throttles);
        }
        else
        {
            \Session::flash('error', trans('auth.OTP.invalid'));

            $request->session()->put('url', $request->session()->get('url'));

            return \Redirect::route('auth.otp.show');
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $user
     * @param                          $throttles
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleOtpOrLogin(Request $request, $user, $throttles)
    {
        if ($user->has2fa())
        {
            $request->session()->put('password_validated', true);
            $request->session()->put('id', $user->id);
            $request->session()->put('remember', $request->has('remember'));

            return \Redirect::route('auth.otp.show');
        }

        $this->auth->login($user, $request->session()->pull('remember'));
        return $this->handleUserWasAuthenticated($request, $throttles);
    }

    public function getLogout()
    {
        \Auth::guard($this->getGuard())->logout();
        \Session::flush();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }
}
