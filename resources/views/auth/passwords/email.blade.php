
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Bootstrap 4 Admin Template">
    <meta name="author" content="Bootlab">

    <title>Login | Shabiki News</title>

    @include('includes.css')
    <style>
        .login-bg{
            background-image: url(https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80);
            background-size: cover;
            background-position: center;
        }
        .no-overflow{
            overflow: hidden;
        }
        .adminkit{
            display: none !important;
        }
    </style>
</head>

<body class="no-overflow">

    <main class="main h-100 w-100">
    <div class="container h-100">
        <div class="row h-100">
            <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                <div class="d-table-cell align-middle">

                    <div class="text-center mt-4">
                        <h1 class="h2"> </h1>
                        <p class="lead">
                            Reset Password
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="m-sm-4">
                                <div class="text-center">

                                </div>
                                <div class="text-center d-none">
                                    <img src="{{ asset('assets/images/logo.svg') }}"  width="260px" />
                                </div>
                                <form action="{{ route('password.email') }}" method="POST">
                                    @csrf
                                    <div class="form-group mt-5">
                                        <label>Email Address</label>
                                        <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                                        @if($errors->has('email'))
                                            <div class="error text-danger mt-2">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>

                                    <div class="text-center mt-3">
                                        <button class="btn btn-lg btn-primary">Send Password Reset Link</button>
                                        <!-- <button type="submit" class="btn btn-lg btn-primary">Sign in</button> -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>
    @include('includes.js')
</body>

</html>
