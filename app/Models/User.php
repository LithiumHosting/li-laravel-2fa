<?php

namespace App\Models;


use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract {
    use Authenticatable, Authorizable, CanResetPassword;

    protected $table = 'users';
    protected $guarded  = ['id'];
    protected $hidden   = ['password', 'remember_token'];
    protected $fillable = ['firstname', 'lastname', 'email', 'password', 'otp_secret'];

    public function has2fa()
    {
        return ! empty($this->otp_secret);
    }
}
