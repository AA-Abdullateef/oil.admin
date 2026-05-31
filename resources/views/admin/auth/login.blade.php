@extends('layouts.auth')
@section('title', 'Admin Login')
@section('content')

<div class="login-wrap">
    <div class="login-brand">
        <div class="name">{{ config('app.name') }}</div>
        <div class="label">Admin Console</div>
    </div>

    <div class="login-card">
        @if($errors->any())
        <div class="form-error" style="margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    placeholder="admin@example.com"
                    autofocus
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required
                >
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">Sign in</button>
        </form>
    </div>

    <div class="login-footer">
        {{ config('app.name') }} - restricted access
    </div>
</div>
@endsection
