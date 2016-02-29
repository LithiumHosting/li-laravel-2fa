@extends('layouts.default')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <h3>Two-Factor Authentication</h3>
            <form method="POST" action="/auth/otp" accept-charset="UTF-8" role="form" autocomplete="false" class="form-horizontal">
                <input name="_token" type="hidden" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="otp" class="col-sm-3 control-label">One-Time Password</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-qrcode"></i></span>
                        <input class="form-control" id="otp" pattern="\d{6}" required="required" name="otp" type="text">
                    </div>
                    <p class="help-block col-sm-offset-3">Enter the 6 digit code from the Google Authenticator App on your phone.</p>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3">
                        <button type="submit" class="btn btn-success">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#otp').focus();
</script>
@endsection
