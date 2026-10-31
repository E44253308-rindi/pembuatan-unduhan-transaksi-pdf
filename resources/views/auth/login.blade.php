@extends('layouts.app')

@section('title', 'Login KomikStore')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Login KomikStore</h3>
                <form method="POST" action="{{ route('login') }}">
                @csrf
                <label class="form-label" for="email">Email</label>
                <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                <label class="form-label" for="password">Password</label>
                <input class="form-control mb-3" id="password" type="password" name="password" required>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="remember" type="checkbox" name="remember" value="1">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
