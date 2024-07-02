<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img\cleora-small.png') }}" type="image/x-icon">
    <title>Login Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <style>
        body, html {
            height: 100%;
            background: #f0f0f0;
            margin: 0;
        }
        .login-container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            animation: fadeIn 1s;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #9b59b6;
        }
        .btn-primary {
            background-color: #9b59b6;
            border-color: #9b59b6;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #884ea0;
            border-color: #7d3c98;
        }
        .form-group {
            position: relative;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #9b59b6;
        }
        .form-control {
            padding-left: 2.375rem;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 100%;
        }
        .image-vector {
            display: block;
            margin: 0 auto 20px;
            max-width: 50%;
        }
        .title {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .login-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    @php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
    @php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
    @php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

    @if (config('adminlte.use_route_url', false))
        @php( $login_url = $login_url ? route($login_url) : '' )
        @php( $register_url = $register_url ? route($register_url) : '' )
        @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
    @else
        @php( $login_url = $login_url ? url($login_url) : '' )
        @php( $register_url = $register_url ? url($register_url) : '' )
        @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
    @endif

    <div class="login-container">
        <div class="login-box">
            <img src="{{ asset('/img/image_vector.png') }}" class="image-vector" alt="Vector Image">
            <!-- <img src="{{ asset('/img/cleora-logo.png') }}" alt="Logo" class="logo"> -->
             <h2 class="text-center">Clerina Group</h2>
            <form action="{{ route('auth.absensi-login-verify') }}" method="post">
                @csrf
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <label for="email" class="sr-only">Email address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Enter email" value="{{ old('email') }}" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">
                                {{ __('adminlte::adminlte.remember_me') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="fas fa-sign-in-alt"></span>
                            {{ __('adminlte::adminlte.sign_in') }}
                        </button>
                    </div>
                </div>
                <!-- <div class="text-center mt-3">
                    <a href="{{ $password_reset_url }}">Forgot password?</a>
                </div> -->
            </form>
            @if(session('message'))
                <div class="alert alert-success mt-3">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
